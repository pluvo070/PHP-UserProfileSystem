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

header('Content-Description: File Transfer'); // 告知浏览器这是一个文件传输请求
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($target) . '"');
header('Content-Length: ' . filesize($target));
// 读取文件并输出到输出缓冲区, 使得文件内容被发送到浏览器, 触发下载
readfile($target);
exit;
