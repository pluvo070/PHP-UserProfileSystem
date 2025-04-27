<?php

// 开启php会话功能, 允许在多个页面之间保存用户信息
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // 如果用户未登录，重定向到登录页面
    exit(); // 终止该脚本执行
}

// 从会话中获取用户信息
$user = $_SESSION['user'];
?>

<!-- 显示欢迎信息 -->
<h1>欢迎, 
    <?php echo htmlspecialchars($user['username']); ?>
</h1>
<p>邮箱: 
    <?php echo htmlspecialchars($user['email']); ?>
</p>
<!-- 显示用户头像 -->
<p>
    <img src="<?php echo $user['avatar']; ?>" alt="Avatar" width="100">
</p>
<!-- 注销链接 -->
<a href="logout.php">退出</a>
