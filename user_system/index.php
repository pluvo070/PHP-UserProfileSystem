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
        <title>用户系统</title>
        <link rel="stylesheet" href="style.css">
        <script src="script.js" defer></script>
    </head>

    <body>
        <div class="login-box">
            <h2>用户登录</h2>
            <!-- 登录表单: 表单内容提交到login.php文件进行处理 -->
            <form action="login.php" method="post" onsubmit="return validateForm()">
            <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
                <input type="submit" value="登录">
            </form>
            <hr> <!-- 分割线 -->
            <!-- 注册链接 -->
            <p>没有账号？ <a href="register.php">注册</a></p>
        </div>
    </body>
</html>
