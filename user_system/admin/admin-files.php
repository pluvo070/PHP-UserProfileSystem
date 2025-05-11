<?php

include __DIR__ . '/config.php';
include __DIR__ . '/init.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.php");
    exit();
}

// 根目录的绝对路径(只允许访问这个目录及其子目录)
$realBase = realpath(__DIR__ . '/../../lib/ueditor-uploads');

// 拼接获取目标路径
$relPath = $_GET['path'] ?? '';// 获取 URL 中的路径参数, 没有则为空
$target = $realBase . '/' . $relPath;
$target = str_replace(['\\', '//'], '/', $target); // 兼容 Windows 和多斜杠

// 获取目标真实路径:
/*  虽然 $target 是绝对路径拼接的, 但是它可能是伪造的、不存在的、或者含有路径穿越符号
    所以需要用 realpath 解析出真实路径 */
$realTarget = realpath($target) ?: $target; // 获取目标路径的真实路径, 如果失败则使用目标路径

// 检查目标路径是否在根目录下
if (strpos($realTarget, $realBase) !== 0) {
    http_response_code(403);
    die("非法路径禁止访问 Access to Invalid Path Denied");
}

// 获取当前目录下所有文件和目录(如果不是目录则赋值为空数组)
$itemList = is_dir($realTarget) ? scandir($realTarget) : [];

?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>文件管理</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-files">
        <h1>文件管理</h1>
        <a href="admin-dashboard.php" style="float: right; margin: 0 20px 10px 0;">⬅️返回面板</a> <!-- 元素浮动显示在右侧 -->
        <h3>当前路径：/<?php echo htmlspecialchars($relPath); ?></h3>
        <?php if ($relPath): ?>
            <a href="admin-files.php?path=<?php echo urlencode(dirname($relPath)); ?>">返回上级目录</a><br><br>
        <?php endif; ?>
        <br>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>图标</th>
                    <th>名称</th>
                    <th>修改时间</th>
                    <th>大小</th>
                    <th>路径</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // 遍历当前目录下的所有文件和目录
            foreach ($itemList as $item) {
                // 跳过当前目录和上级目录
                if ($item === '.' || $item === '..') continue;
                // 构造完整路径
                $fullPath = $realTarget . '/' . $item;
                $relativeItemPath = ltrim($relPath . '/' . $item, '/'); // 拼接路径, 并去掉路径开头的斜杠
                $isDir = is_dir($fullPath); // 判断是否为目录
                // 输出表格内容
                echo "<tr>"; 
                echo "<td>" . ($isDir ? "📁" : "📄") . "</td>";
                echo "<td>" . htmlspecialchars($item) . "</td>"; // 文件名
                echo "<td>" . date("Y-m-d H:i:s", filemtime($fullPath)) . "</td>"; // 日期
                echo "<td>" . ($isDir ? '--' : filesize($fullPath) . ' 字节') . "</td>"; // 大小
                echo "<td>" . htmlspecialchars($relativeItemPath) . "</td>"; // 路径(从根目录开始的相对路径)
                echo "<td>";
                // 根据是文件还是目录, 输出不同操作类型
                if ($isDir) {
                    // urlencode(): URL 编码, 用于在 URL 中传递安全的参数(后续 PHP 获取该参数会自动解码)
                    echo "<a href='admin-files.php?path=" . urlencode($relativeItemPath) . "'>进入</a>";
                } else {
                    echo "<a href='file-download.php?file=" . urlencode($relativeItemPath) . "'>下载</a> | ";
                    echo "<a href='file-edit.php?file=" . urlencode($relativeItemPath) . "'>编辑</a> | ";
                    echo "<a href='file-delete.php?file=" . urlencode($relativeItemPath) . "' onclick='return confirm(\"确认删除？\");'>删除</a>";
                }
                echo "</td></tr>";
            }
            ?>
            </tbody>
        </table>
        <br><br>

        <!-- 文件上传表单 -->
        <form method="POST" action="file-upload.php" enctype="multipart/form-data">
            <?php csrf_input_field(); ?> <!-- 发送隐藏的token -->
            <input type="file" name="upload_file" required>
            <input type="hidden" name="upload_path" value="<?php echo htmlspecialchars($relPath); ?>">
            <button type="submit">上传文件</button>
        </form>
        
    </div>
</body>
</html>
