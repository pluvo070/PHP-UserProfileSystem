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

// 替换头像
$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($avatar);
$stmt->fetch();
$stmt->close();
$template = str_replace('{avatar}', htmlspecialchars($avatar), $template);

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

// 替换头图背景
$bgDir = 'uploads_infobg/' . $username . '/';
$bgimage = ''; // 默认空背景
if (is_dir($bgDir)) {
    // 匹配支持的图片格式
    $files = glob($bgDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    if (!empty($files)) {
        // 取第一个图片(实际上只有一张)
        $bgimage = $files[0];
    }
}
$template = str_replace('{bgimage}', htmlspecialchars($bgimage), $template);

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

// 照片展示
$photosHtml = '';
$dir = 'uploads_photo/' . $username . '/';
if (is_dir($dir)) {
    $files = glob($dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    usort($files, function ($a, $b) { // 使用 filemtime 进行时间倒序排序(最新头像在最前面)
        return filemtime($b) - filemtime($a);
    });
    foreach ($files as $file) {
        $basename = basename($file);
        $photosHtml .= '<img src="' . htmlspecialchars($file) . ' "class="photo-thumbnail"> '; // 原始头像获取方法(较快, 但不能使用.htaccess限制)
    }
}
$template = str_replace('{photo_gallery}', $photosHtml, $template);

// 替换 {csrf_input}
ob_start();
csrf_input_field();
$csrfInput = ob_get_clean();
$template = str_replace('{csrf_input}', $csrfInput, $template);

// 用户博客展示
$blogsHtml = '';
$stmt = $conn->prepare("
    SELECT b.id, b.content, b.created_at, b.ipaddr, b.uagent, u.username, u.avatar
    FROM user_blogs AS b
    JOIN users AS u ON b.user_id = u.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blogsHtml .= "<div class='blogs'>";
        $blogsHtml .= "<img src='" . htmlspecialchars($row['avatar']) . "' class='blogs-avatar'>";
        $blogsHtml .= "<strong class='blogs_username'>" . htmlspecialchars($row['username']) . "</strong>";
        $blogsHtml .= "   <em class='blogs_timestamp'>" . htmlspecialchars($row['created_at']) . "</em><br>";
        $blogsHtml .= "<p class='blogs_content'>" . $row['content'] . "</p>";
        $blogsHtml .= "<small class='blogs_info'>IP: " . htmlspecialchars($row['ipaddr']) . " | From: " . htmlspecialchars($row['uagent']) . "</small>";
        $blogsHtml .= "</div>";
    }
} else {
    $blogsHtml = "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;暂无博客内容。</p>";
}

$stmt->close();

// 替换模板变量
$template = str_replace('{user_blogs}', $blogsHtml, $template);

// 输出页面
echo $template;
