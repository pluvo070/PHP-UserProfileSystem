<?php

// 用于展示用户面板页面, html 部分取用模板 templates/dashboard.tpl.html

include __DIR__ . '/admin/config.php';
include __DIR__ . '/admin/init.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];
$user_id = $user['id'];

// 读取模板
$template = file_get_contents(__DIR__ . '/templates/dashboard.tpl.html');

// 替换用户名
$template = str_replace('{username}', htmlspecialchars($username), $template);

// 替换在 html 中隐藏发送的 token
//$template = str_replace('{csrf_input_field}', csrf_input_field(), $template);
$template = str_replace('{csrf_token}', $_SESSION['csrf_token'], $template);

// 替换签名
$stmt = $conn->prepare("SELECT signature FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($signature);
$stmt->fetch();
$stmt->close();
$template = str_replace('{signature}', htmlspecialchars($signature), $template);

// 历史头像展示
$galleryHtml = '';
$dir = 'uploads_avatar/' . $username . '/';
if (is_dir($dir)) {
    $files = glob($dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    usort($files, function ($a, $b) { // 使用 filemtime 进行时间倒序排序(最新头像在最前面)
        return filemtime($b) - filemtime($a);
    });
    foreach ($files as $file) {
        $basename = basename($file);
        $galleryHtml .= '<img src="' . htmlspecialchars($file) . ' "class="avatar-thumbnail"> '; // 原始头像获取方法(较快, 但不能使用.htaccess限制)
    }
}
$template = str_replace('{avatar_gallery}', $galleryHtml, $template);

// 精选照片展示
$photosHtml = '';
$stmt = $conn->prepare("SELECT filepath FROM user_photos WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $filepath = htmlspecialchars($row['filepath']);
    $photosHtml .= "<div class='photo-item'><img src='$filepath'><form method='post' action='photo-delete.php' onsubmit='return confirm(\"确定删除？\")'><input type='hidden' name='filepath' value='$filepath'><input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'><button type='submit'>删除</button></form></div>";
}
$stmt->close();
$template = str_replace('{photo_gallery}', $photosHtml, $template);

// 用户博客展示
$blogsHtml = '';
$stmt = $conn->prepare("SELECT id, title, content, created_at FROM user_blogs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $title = htmlspecialchars($row['title']);
    $content = nl2br(htmlspecialchars($row['content']));
    $time = $row['created_at'];
    $blogId = $row['id'];
    $blogsHtml .= "<div class='blog-entry'><h4>$title</h4><em>$time</em><p>$content</p>
        <form method='post' action='blog-delete.php' onsubmit='return confirm(\"确定删除这篇博客？\")'>
            <input type='hidden' name='id' value='$blogId'>
            <input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>
            <button type='submit'>删除</button>
        </form>
    </div><hr>";
}
$stmt->close();
$template = str_replace('{user_blogs}', $blogsHtml, $template);

// 输出页面
echo $template;
