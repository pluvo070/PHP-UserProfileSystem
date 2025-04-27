<?php
// 单独的文件上传

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['file']['tmp_name'];
        $name = $_FILES['file']['name'];
        $upload_dir = 'uploads_file/';
        $target_file = $upload_dir . basename($name);

        // 确保文件没有重名
        if (move_uploaded_file($tmp_name, $target_file)) {
            echo "文件上传成功！文件保存在：$target_file";
        } else {
            echo "文件上传失败！";
        }
    } else {
        echo "没有选择文件或上传失败！";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件上传</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>文件上传</h2>
<form action="upload.php" method="post" enctype="multipart/form-data">
    选择文件: <input type="file" name="file" required><br>
    <button type="submit">上传</button>
</form>

</body>
</html>
