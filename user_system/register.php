<?php

include __DIR__ . '/admin/config.php'; // 连接数据库
include __DIR__ . '/admin/init.php'; // 初始化会话和token


// 处理注册逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单提交的用户名、密码和邮箱
    $username = $_POST['username'];
    $password = trim($_POST['password']);
    $email = $_POST['email'];
    $avatar = ''; // 初始化头像路径为空

    // 检查用户名和密码的长度是否>=3
    if (strlen($username) >= 3 && strlen($password) >= 3) {
        // 检查用户名是否已经存在
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            echo "用户名已被注册！";
        } else {
            addNewUser($username, $password, $email, $avatar, $conn); 
            /* $conn是config.php中定义的数据库连接对象 */
        }
    }
}


function addNewUser($username, $password, $email, $avatar, $conn){
    // 对密码进行哈希加密
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 创建用户文件夹, 用于保存所有头像
    $userDir = 'uploads_avatar/' . $username . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0777, true); // 递归创建目录
    }

    // 检查文件上传是否成功
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        
        $tmp_name = $_FILES['avatar']['tmp_name']; // 上传的临时文件
        $originalName = $_FILES['avatar']['name']; // 原始文件名
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

        // 设置头像初始编号为 0
        $index = 0;
        do {
            $newName = $username . '_avatar_' . $index . '.' . $ext; // 拼接文件名
            $avatarPath = $userDir . $newName;
            $index++;
        } while (file_exists($avatarPath));

        // 移动文件
        move_uploaded_file($tmp_name, $avatarPath);

        // 保存到数据库的路径(相对路径)
        $avatar = $avatarPath;
    }

    // 保存用户信息到数据库
    /* users 表里最后一列 avatar 保存的是每个用户对应的头像路径 */
    $sql = "INSERT INTO users (username, password, email, avatar) VALUES ('$username', '$passwordHash', '$email', '$avatar')";
    // 执行 SQL 查询
    if ($conn->query($sql) === TRUE) {
        echo "注册成功！";
    } else {
        echo "错误: " . $sql . "<br>" . $conn->error;
    }
}

?>




<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="register-box">
        <h2>注册新账号</h2>
        <!-- 注册表单 -->
        <form method="post" enctype="multipart/form-data">
            <?php csrf_input_field(); ?><!-- 发送隐藏的token -->
            <label for="username">用户名:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="username">密码:</label>
            <input type="password" id="username" name="password" required><br>
            <label for="username">邮箱:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="username">头像:</label>
            <input type="file" id="avatar" name="avatar"><br><br>
            <button type="submit">注册</button>
        </form>
        <hr>
        <p>已有账号？<a href="index.php">点击登录</a></p>
    </div>
</body>
</html>