<?php
// 连接数据库
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $avatar = null;

    if ($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $name = $_FILES['avatar']['name'];
        $avatar = "uploads/" . $name;
        move_uploaded_file($tmp_name, $avatar);
    }

    $sql = "INSERT INTO users (username, password, email, avatar) VALUES ('$username', '$password', '$email', '$avatar')";
    if ($conn->query($sql) === TRUE) {
        echo "注册成功！";
    } else {
        echo "错误: " . $sql . "<br>" . $conn->error;
    }
}
?>

<form method="post" enctype="multipart/form-data">
    用户名: <input type="text" name="username" required><br>
    密码: <input type="password" name="password" required><br>
    邮箱: <input type="email" name="email" required><br>
    头像: <input type="file" name="avatar"><br>
    <button type="submit">注册</button>
</form>
