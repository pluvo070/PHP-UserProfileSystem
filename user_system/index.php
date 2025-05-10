<?php

// 初始化会话和token
include __DIR__ . '/admin/init.php';

// 判断用户是否已经登录($_SESSION的user键已经有值)
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php"); // 已经登录，跳转到用户中心页面
    /* header是内置函数, 用来发送原始HTTP头信息, Location: url用于跳转 */
    exit(); // 终止当前脚本
}

// 取出之前提交的错误和表单数据: 用于把错误信息显示在页面上, 并且自动填入上一次输入的用户名
$error = $_SESSION['error'] ?? ''; // 双问号是空合并运算符, 前者不为空就赋值为前者, 否则赋值为后者
$old_username = $_SESSION['old_username'] ?? ''; 

// 清空 session 中临时数据
unset($_SESSION['error'], $_SESSION['old_username']);
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

            <!-- 错误提示: 如果之前输入了错误的登录信息, 就新建一个div元素来展示信息 -->
            <?php if (!empty($error)): ?>
                <div class="error-message" style="color:red;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- 登录表单: 表单内容提交到login.php文件进行处理 -->
            <form action="login.php" method="post" id="loginForm" novalidate> <!-- JS内联写法:onsubmit="return validateForm()" -->
                <?php csrf_input_field(); ?> <!-- 发送隐藏的token -->
                <label for="username">用户名:</label>
                <!-- value属性: 用户名处自动填入上一次填写的用户名 -->
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($old_username); ?>" required>
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
                <br> <!-- 空行 -->
                <button type="submit">登录</button>
                <br> 
            </form>
            <hr> <!-- 分割线 -->
            <!-- 注册链接 -->
            <p>没有账号？ <a href="register.php">注册</a></p>
        </div>
        
    </body>
</html>
