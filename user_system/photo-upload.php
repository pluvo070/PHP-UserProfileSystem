<?php

// 用于处理上传照片的逻辑, 不展示页面

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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['photo'])) {

    $tmp_name = $_FILES['photo']['tmp_name']; // 上传的临时文件
    $originalName = $_FILES['photo']['name']; // 原始文件名
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
    $userDir = 'uploads_photo/' . $username . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0777, true);
    }

    // 找到新的编号，避免文件名冲突
    $index = 0;
    do {
        $newName = $username . '_photo_' . $index . '.' . $ext;
        $avatarPath = $userDir . $newName;
        $index++;
    } while (file_exists($avatarPath));

    // 移动上传的文件到目标位置
    if (!move_uploaded_file($tmp_name, $avatarPath)) {
        echo json_encode(['error' => '照片上传失败，请重试']);
        exit();
    }

    echo json_encode(['success' => true]); // 上传成功

} else {
    echo json_encode(['error' => '无效的请求']);
    exit();
}
?>
