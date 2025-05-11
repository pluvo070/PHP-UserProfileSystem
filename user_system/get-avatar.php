<?php

// get-avatar.php - 安全输出用户头像

/* 原来直接在 dashboard 的 html 部分中引入图片, 会在前端暴露项目存储结构;
   现使用参数传递给该 php 文件, 由该文件解析图片真实路径并读取头像数据, 
   然后直接把图像加在响应体中返回给前端浏览器, 
   相当于一个代理服务器, 这样就不会暴露项目结构 */

include __DIR__ . '/admin/config.php';
include __DIR__ . '/admin/init.php'; 

function sendImage($path) {
    if (!file_exists($path) || !is_file($path)) {
        http_response_code(404);
        exit("头像文件不存在");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path); // 头像文件的真实类型
    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'])) {
        http_response_code(403);
        exit("不允许的图片类型");
    }

    // 用 HTTP 协议返回一张图片, 而不是返回 HTML
    header("Content-Type: $mime"); // 告诉浏览器返回的不是 HTML，而是图片
    header("Content-Length: " . filesize($path)); // 头像图片的真实大小
    readfile($path); // 读取图片文件内容，然后把图片的内容输出到响应体进行返回
    exit;
}

$username = $_GET['user'] ?? ($_SESSION['user']['username'] ?? null);
if (!$username) {
    http_response_code(401);
    exit("未登录或未指定用户");
}

// 获取头像路径
if (isset($_GET['file'])) { 
    // 已经指定了历史头像文件名, 则是该用户的全部历史头像, 需要身份验证为该用户
    $safeFilename = basename($_GET['file']); // 防止路径穿越
    $avatarPath = __DIR__ . DIRECTORY_SEPARATOR. "uploads_avatar". DIRECTORY_SEPARATOR. "$username". DIRECTORY_SEPARATOR. "$safeFilename";
    $avatarPath = realpath(__DIR__ . "/uploads_avatar/$username/$safeFilename");
    // 验证路径是否还在用户的目录内(防止绕过 basename)
    $userDir = realpath(__DIR__ . "/uploads_avatar/$username/");
    if (!$avatarPath || strpos($avatarPath, $userDir) !== 0) {
        http_response_code(403);
        exit("非法文件访问");
    }
    sendImage($avatarPath);
} else { 
    // 未指定历史头像名, 则是该用户和其他用户的最新头像 → 查询数据库中的最新头像路径
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $relativePath = $row['avatar'];
        $avatarPath = realpath(__DIR__ . '/' . $relativePath);
        if (!$avatarPath || strpos($avatarPath, realpath(__DIR__ . '/uploads_avatar/')) !== 0) {
            http_response_code(403);
            exit("头像路径无效");
        }
        sendImage($avatarPath);
    } else {
        //echo "调试：头像路径不存在 -> " . $avatarPath;
        http_response_code(404);
        exit("用户不存在");
    }
}
