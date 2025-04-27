<?php
// 连接数据库
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单提交的用户名、密码和邮箱
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);// 对密码进行哈希加密
    $email = $_POST['email'];
    $avatar = null; // 初始化头像变量，默认为 null

    // 检查文件上传是否成功
    if ($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['avatar']['tmp_name']; // 临时文件名
        $name = $_FILES['avatar']['name']; // 上传的文件名
        $avatar = "uploads_avatar/" . $name; // 设置保存路径
        move_uploaded_file($tmp_name, $avatar); // 移动上传的文件到指定目录
    }

    // 准备插入 SQL 语句
    /* users 表里最后一列 avatar 保存的是每个用户对应的头像路径 */
    $sql = "INSERT INTO users (username, password, email, avatar) VALUES ('$username', '$password', '$email', '$avatar')";
    // 执行 SQL 查询
    if ($conn->query($sql) === TRUE) {
        echo "注册成功！";
    } else {
        echo "错误: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!-- 注册表单 -->
<form method="post" enctype="multipart/form-data">
    用户名: <input type="text" name="username" required><br>
    密码: <input type="password" name="password" required><br>
    邮箱: <input type="email" name="email" required><br>
    头像: <input type="file" name="avatar"><br>
    <button type="submit">注册</button>
</form>
