<?php

// 用于处理重新上传头像的逻辑, 不展示页面

session_start();
include __DIR__ . '/admin/config.php';

// 检查用户是否已登录
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 从会话中获取用户信息
$user = $_SESSION['user'];
$username = $user['username'];

// 处理重新上传头像
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['avatar'])) {
    $userDir = 'uploads_avatar/' . $username . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0777, true);
    }

    $tmp_name = $_FILES['avatar']['tmp_name'];
    $originalName = $_FILES['avatar']['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);

    // 找到新的编号
    $index = 0;
    while (file_exists($userDir . $username . '_avatar_' . $index . '.' . $ext)) {
        $index++;
    }
    $newName = $username . '_avatar_' . $index . '.' . $ext;
    $avatarPath = $userDir . $newName;

    move_uploaded_file($tmp_name, $avatarPath);

    // 更新数据库中最新的头像路径
    $sql = "UPDATE users SET avatar = '$avatarPath' WHERE id = " . $user['id'];
    if ($conn->query($sql) === TRUE) {
        // 同时更新 session
        $_SESSION['user']['avatar'] = $avatarPath;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "上传失败: " . $conn->error;
    }
} else {
    echo "无效请求";
}
?>
