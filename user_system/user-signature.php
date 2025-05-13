<?php

// 用于处理用户异步更新个性签名，不展示页面

include __DIR__ . '/admin/config.php'; // 数据库配置
include __DIR__ . '/admin/init.php';   // 初始化 session 和 CSRF

// 设置响应为 JSON 格式
header('Content-Type: application/json');

// 检查是否已登录
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 获取用户信息
$user = $_SESSION['user'];
$userId = $user['id'];

// 检查请求是否合法
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signature'])) {

    // 获取并处理签名内容
    $signature = trim($_POST['signature']);
    if (mb_strlen($signature) > 200) {
        echo json_encode(['error' => '个性签名不能超过 200 字']);
        exit();
    }

    // 更新数据库
    $stmt = $conn->prepare("UPDATE users SET signature = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['error' => '数据库预处理失败']);
        exit();
    }

    $stmt->bind_param("si", $signature, $userId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['error' => '数据库更新失败']);
        exit();
    }

} else {
    echo json_encode(['error' => '无效的请求']);
    exit();
}
