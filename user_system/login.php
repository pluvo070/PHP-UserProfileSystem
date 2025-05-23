<?php

// 本脚本专门用于登录信息的处理, 而不是给用户展示的页面

include __DIR__ . '/admin/config.php'; // 连接数据库
include __DIR__ . '/admin/init.php'; // 初始化会话和token


// 处理登录数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. 接收输入的账号密码
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 2. 判断账号密码的正确性
    // 检查用户名和密码的长度是否>=3
    if (strlen($username) < 3 || strlen($password) < 3) {
        $_SESSION['error'] = '用户名和密码长度必须 >= 3';
        $_SESSION['old_username'] = $username; // 存储错误信息到会话
        header('Location: index.php');
        exit;
    }

    // 查询数据库中的用户名和密码
    //$sql = "SELECT * FROM users WHERE username = '$username'"; // 创建SQL查询
    //$result = $conn->query($sql); // 执行SQL查询
    // 使用预处理语句防止 SQL 注入
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 检查查询结果
    //if ($result->num_rows > 0) {
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // 验证密码
        if (password_verify($password, $user['password'])) { // 验证加密后的密码
        //if ($password === $row['password']) { // 验证明文密码
            // 3. 成功登录, 保存登录信息
            $_SESSION['user'] = $user; // 记录这一次会话的user键的值
            // 4. 跳转至成功登陆的页面
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


