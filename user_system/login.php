<?php

// 连接数据库
include 'config.php';

// 处理登录数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 查询数据库中的用户名和密码
    $sql = "SELECT * FROM users WHERE username = '$username'"; // 创建SQL查询
    $result = $conn->query($sql); // 执行SQL查询

    // 检查查询结果
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // 验证密码
        if (password_verify($password, $user['password'])) { // 验证加密后的密码
        //if ($password === $row['password']) { // 验证明文密码
            session_start();
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "密码错误！";
        }
    } else {
        echo "用户名不存在！";
    }
}
?>

<form method="post">
    用户名: <input type="text" name="username" required><br>
    密码: <input type="password" name="password" required><br>
    <button type="submit">登录</button>
</form>
