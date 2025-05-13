<?php

// 用于展示用户面板页面, html 部分取用模板 templates/dashboard.tpl.html

include __DIR__ . '/admin/config.php'; // 引入数据库配置
include __DIR__ . '/admin/init.php'; // 引入会话配置和token检测

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];

// 加载模板
$template = file_get_contents(__DIR__ . '/templates/dashboard.tpl.html');

// 替换用户名 {username}
$template = str_replace('{username}', htmlspecialchars($username), $template);

// 替换在html中隐藏发送的token
$template = str_replace('{csrf_token}', $_SESSION['csrf_token'], $template);

// 替换历史头像 {avatar_gallery}
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
        //$galleryHtml .= '<img src="get-avatar.php?file=' . urlencode($basename) . '" class="avatar-thumbnail">'; // 通过 php 代理获取头像(较慢)
        // 若使用懒加载,可写为:
        // $galleryHtml .= '<img data-src="' . htmlspecialchars($file) . '" class="avatar-thumbnail lazyload">';
    }
}
$template = str_replace('{avatar_gallery}', $galleryHtml, $template);

// 替换 {csrf_input}
ob_start();
csrf_input_field();
$csrfInput = ob_get_clean();
$template = str_replace('{csrf_input}', $csrfInput, $template);

// 替换 {gbook_messages}
$messagesHtml = ''; // 用于存储留言列表的全部内容
$sql = " 
    SELECT gbook.username, gbook.content, gbook.ipaddr, gbook.uagent, gbook.created_at, users.avatar
    FROM gbook
    LEFT JOIN users ON gbook.username = users.username
    ORDER BY gbook.created_at DESC
"; // 查询留言记录, 并联合users表获得该用户的最新头像路径
$result = $conn->query($sql);// 获取所有查询结果
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {// 遍历结果的每一行
        $messagesHtml .= "<div class='message'>";
        $messagesHtml .= "<img src='" . htmlspecialchars($row['avatar']) . "' class='gbook-avatar'>"; // 原始头像获取方法(较快, 但不能使用.htaccess限制)
        //$messagesHtml .= "<img src='get-avatar.php?user=" . urlencode($row['username']) . "' class='gbook-avatar'>"; // 通过 php 代理获取头像(较慢)
        $messagesHtml .= "<strong class='gbook_username'>" . htmlspecialchars($row['username']) . "</strong>";
        $messagesHtml .= "  <em class='gbook_timestamp'>" . $row['created_at'] . "</em><br>";
        $messagesHtml .= "<p class='gbook_content'>" . $row['content'] . "</p>";
        $messagesHtml .= "<small class='gbook_info'>IP: " . htmlspecialchars($row['ipaddr']) . " | From: " . htmlspecialchars($row['uagent']) . "</small>";
        $messagesHtml .= "</div>";
    }
} else {
    $messagesHtml = "暂无留言。";
}
$template = str_replace('{gbook_messages}', $messagesHtml, $template);

// 输出最终内容
echo $template;
