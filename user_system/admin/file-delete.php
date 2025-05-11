<?php
include __DIR__ . '/config.php';
include __DIR__ . '/init.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    die("未授权访问 Unauthorized Access");
}

$basePath = realpath(__DIR__ . '/../../lib/ueditor-uploads'); 
$relPath = $_GET['file'] ?? ''; 
$target = realpath($basePath . '/' . $relPath); // 拼接路径

// 检查是否存在、是否是文件、是否属于 basePath 之下
if (!$target || strpos($target, $basePath) !== 0 || !is_file($target)) {
    die("无效文件路径 Invalid File Path");
}

if (unlink($target)) { // 删除文件
    // 成功删除后重定向到文件管理页面
    header("Location: admin-files.php?path=" . urlencode(dirname($relPath)));
} else {
    die("删除失败 Deletion Failed");
}
