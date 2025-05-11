<?php
include __DIR__ . '/config.php';
include __DIR__ . '/init.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    die("未授权访问 Unauthorized Access");
}

$basePath = realpath(__DIR__ . '/../../lib/ueditor-uploads'); // 根目录绝对路径
$subPath = $_POST['path'] ?? ''; // URL 中传递的相对路径
$targetDir = realpath($basePath . '/' . $subPath); // 拼接路径

// 防止路径穿越攻击
if ($targetDir === false || strpos($targetDir, $basePath) !== 0 || !is_dir($targetDir)) {
    http_response_code(400);
    die("非法上传路径 Invalid File Path");
}

// 文件上传逻辑
if (!isset($_FILES['upload_file'])) {
    http_response_code(400);
    die("未选择文件 No file chosed");
}

$file = $_FILES['upload_file'];
$maxSize = 20 * 1024 * 1024; // 20MB

// 限制文件大小
if ($file['size'] > $maxSize) {
    http_response_code(400);
    die("文件过大, 最大限制为 20MB");
}

// 获取文件名和扩展名
$originalName = basename($file['name']); // 文件原始名称
$ext = pathinfo($originalName, PATHINFO_EXTENSION); // 原始扩展名
$extLower = strtolower($ext); // 原始扩展名小写

// 将文件名中的危险字符(所有不是字母, 数字, 下划线, 点或连字符的字符)全部替换为下划线, 防止文件名注入
$safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $ext;

// MIME 类型验证
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realMime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

// 允许的文件类型
$allowedMimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'pdf' => 'application/pdf',
    'txt' => 'text/plain',
    'zip' => 'application/zip',
];

// 将原始扩展名转换为 MIME 类型(这里只转换允许的MIME类型, 其他类型都未转换, 因此也无法通过下面的判断 )
$expectedMime = $allowedMimeTypes[$extLower] ?? '';

// 判断文件扩展名的 MIME 类型是否是文件真实 MIME 类型
if ($expectedMime !== $realMime) {
    http_response_code(400);
    die("MIME 不匹配, 或不被允许的文件类型");
}

// 移动文件
$destination = $targetDir . '/' . $safeName;
if (move_uploaded_file($file['tmp_name'], $destination)) {
    echo "上传成功: " . htmlspecialchars($safeName);
} else {
    http_response_code(500);
    die("文件上传失败, 请重试");
}

?>