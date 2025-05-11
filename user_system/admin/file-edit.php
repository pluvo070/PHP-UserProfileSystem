<?php
include __DIR__ . '/config.php';
include __DIR__ . '/init.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    die("未授权访问 Unauthorized Access");
}

$basePath = realpath(__DIR__ . '/../../lib/ueditor-uploads'); // 根目录绝对路径
$relPath = $_GET['file'] ?? ''; // URL 中传递的相对路径
$target = realpath($basePath . '/' . $relPath); // 拼接路径

// 检查是否存在、是否是文件、是否属于 basePath 之下
if (!$target || strpos($target, $basePath) !== 0 || !is_file($target)) {
    die("无效文件路径 Invalid File Path");
}

// 如果是 POST 请求，更新文件内容
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newContent = $_POST['content'] ?? '';
    file_put_contents($target, $newContent);
    echo "<script>alert('保存成功'); location.href='admin-files.php?path=" . urlencode(dirname($relPath)) . "';</script>";
    exit;
}

// 读取文件原内容
$content = file_get_contents($target);

?>



<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>编辑文件 - <?php echo htmlspecialchars($relPath); ?></title>
</head>
<body>
    <h2>编辑文件: <?php echo htmlspecialchars($relPath); ?></h2>
    <form method="POST">
        <?php csrf_input_field(); ?><!-- 发送隐藏的token -->
        <textarea name="content" rows="25" cols="100"><?php echo htmlspecialchars($content); ?></textarea><br>
        <button type="submit">保存</button>
        <a href="admin-files.php?path=<?php echo urlencode(dirname($relPath)); ?>">取消</a>
    </form>
</body>
</html>
