<?php

// 用户主页删除自己博客的逻辑处理

include __DIR__ . '/admin/config.php'; // 数据库连接
include __DIR__ . '/admin/init.php';   // session 初始化

header('Content-Type: application/json');

// 判断是否登录
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// 获取 blog_id 参数
if (!isset($_POST['blog_id']) || !is_numeric($_POST['blog_id'])) {
    echo json_encode(['success' => false, 'error' => '参数错误']);
    exit();
}

$blog_id = intval($_POST['blog_id']);

// 删除前先确认是否属于当前用户
$stmt = $conn->prepare("SELECT user_id FROM user_blogs WHERE id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$stmt->bind_result($owner_id);
if ($stmt->fetch()) {
    $stmt->close();
    if ($owner_id !== $user_id) {
        echo json_encode(['success' => false, 'error' => '无权限删除']);
        exit();
    }

    // 执行删除
    $stmt = $conn->prepare("DELETE FROM user_blogs WHERE id = ?");
    $stmt->bind_param("i", $blog_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => '数据库操作失败']);
    }
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'error' => '博客不存在']);
    exit();
}
