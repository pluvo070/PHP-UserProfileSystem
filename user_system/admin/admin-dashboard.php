<?php
include __DIR__ . '/config.php';
include __DIR__ . '/init.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员面板</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-dashboard">
        <h1>管理员面板</h1>
        <a href="admin-logout.php" style="float: right; margin: 0 20px 30px 0;">退出登录</a> <!-- 元素浮动显示在右侧 -->
        <div class="admin-choice" style="clear: both;"><!-- 清除浮动影响 -->
            <ul> 
                <li><a href="admin-gbook.php">📃留言管理</a></li>
                <li><a href="admin-files.php">🗂️文件管理</a></li>
            </ul>
        </div>
    </div>
</body>
</html>