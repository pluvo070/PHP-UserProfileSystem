<?php
// 判断用户是否已经登录，如果已经登录，跳转到用户中心页面
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>用户注册与登录</h2>

<!-- 登录表单 -->
<h3>登录</h3>
<form action="login.php" method="post">
    用户名: <input type="text" name="username" required><br>
    密码: <input type="password" name="password" required><br>
    <button type="submit">登录</button>
</form>

<hr>

<!-- 注册链接 -->
<p>还没有账户？ <a href="register.php">注册</a></p>

</body>
</html>
