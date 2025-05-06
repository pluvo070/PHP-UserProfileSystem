<?php

// 用于处理留言板内容
session_start();
include 'config.php';

// 确保用户已登录
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];

// 获取留言内容
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if (empty($content)) {
        die("留言内容不能为空！");
    }

    // 获取用户IP和UA, 自动存储到数据库中
    $ipaddr = $_SERVER['REMOTE_ADDR'];
    $uagent = $_SERVER['HTTP_USER_AGENT'];

    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("INSERT INTO gbook (username, content, ipaddr, uagent, created_at) VALUES (?, ?, ?, ?, NOW())");

    // 检查 prepare 是否成功
    if ($stmt === false) {
        die("SQL 错误: " . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $content, $ipaddr, $uagent);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "留言失败：" . $conn->error;
    }

    $stmt->close();
} else {
    echo "非法请求。";
}
?>
