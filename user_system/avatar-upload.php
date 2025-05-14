<?php

// 用于处理重新上传头像的逻辑, 不展示页面

include __DIR__ . '/admin/config.php'; // 连接数据库
include __DIR__ . '/admin/init.php'; // 初始化会话和token

// 检查用户是否已登录
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 从会话中获取用户信息
$user = $_SESSION['user'];
$username = $user['username'];

// 检查是否是通过 POST 上传头像
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['avatar'])) {

    $tmp_name = $_FILES['avatar']['tmp_name']; // 上传的临时文件
    $originalName = $_FILES['avatar']['name']; // 原始文件名
    $ext = pathinfo($originalName, PATHINFO_EXTENSION); // 获取扩展名

    // 安全检查：只允许上传指定类型的图片
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

    // 读取真实 MIME 类型
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);

    // 检查 MIME 类型和扩展名是否都合法
    if (!in_array($mimeType, $allowedTypes) || !in_array(strtolower($ext), $allowedExts)) {
        echo json_encode(['error' => '仅允许上传 JPG、PNG、GIF 格式的图片']);
        exit();
    }

    // 创建用户目录(如果不存在)
    $userDir = 'uploads_avatar/' . $username . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0777, true);
    }

    // 清理历史头像: 如果超过8个就删除最旧的
    $files = glob($userDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    if (count($files) >= 8) {
        // 按修改时间排序，从旧到新
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        // 删除第一个（最旧）
        unlink($files[0]);
    }

    // 新头像命名: 使用时间命名避免重复
    $newName = $username . '_avatar_' . time() . '.' . $ext;
    $avatarPath = $userDir . $newName;

    // 移动上传的文件到目标位置
    if (!move_uploaded_file($tmp_name, $avatarPath)) {
        echo json_encode(['error' => '头像上传失败，请重试']);
        exit();
    }

    // 更新数据库中最新的头像路径
    //$sql = "UPDATE users SET avatar = '$avatarPath' WHERE id = " . $user['id'];
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['error' => '数据库预处理失败']);
        exit();
    }
    $stmt->bind_param("si", $avatarPath, $user['id']);
    //if ($conn->query($sql) === TRUE) {
    if ($stmt->execute()) {
        // 同步更新 session 中的头像路径
        $_SESSION['user']['avatar'] = $avatarPath;
        // 这里使用异步 JS 上传, 就不需要重定向回去了
        // header("Location: dashboard.php"); // 上传成功后重定向回 dashboard
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
?>
