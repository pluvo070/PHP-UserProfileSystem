<?php

// 本脚本专门用于登录信息的处理, 而不是给用户展示的页面
session_start();

// 连接数据库
include __DIR__ . '/admin/config.php';

// 处理登录数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 检查用户名和密码的长度是否>=3
    if (strlen($username) < 3 || strlen($password) < 3) {
        $_SESSION['error'] = '用户名和密码长度必须 >= 3';
        $_SESSION['old_username'] = $username; // 存储错误信息到会话
        header('Location: index.php');
        exit;
    }

    // 查询数据库中的用户名和密码
    $sql = "SELECT * FROM users WHERE username = '$username'"; // 创建SQL查询
    $result = $conn->query($sql); // 执行SQL查询

    // 检查查询结果
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // 验证密码
        if (password_verify($password, $user['password'])) { // 验证加密后的密码
        //if ($password === $row['password']) { // 验证明文密码
            // 成功登录, 保存登录信息
            session_start(); // 开启一个会话(这样才能使用$_SESSION这个变量来存储一些数据)
            $_SESSION['user'] = $user; // 记录这一次会话的user键的值
            header("Location: dashboard.php");
            exit();
        } else {
            //echo "密码错误！";
            $_SESSION['error'] = "密码错误！"; // 存储错误信息到会话
            $_SESSION['old_username'] = $username;
            header('Location: index.php');
            exit();
        }
    } else {
        //echo "用户名不存在！";
        $_SESSION['error'] = "用户名不存在！"; // 存储错误信息到会话
        $_SESSION['old_username'] = $username;
        header('Location: index.php');
        exit();
    }
    
}
?>


