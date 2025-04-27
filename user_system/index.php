<?php

// 开启php会话功能
session_start();

// 判断用户是否已经登录($_SESSION的user键已经有值)
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php"); // 已经登录，跳转到用户中心页面
    /* header是内置函数, 用来发送原始HTTP头信息, Location: url用于跳转 */
    exit(); // 终止当前脚本
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
            <form action="login.php" method="post"> <!-- JS内联写法:onsubmit="return validateForm()" -->
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
