<?php

// 开启php会话功能
session_start();

// 检查用户是否已登录($_SESSION的user键已经有值)
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // 如果用户未登录，重定向到登录页面
    exit(); // 终止该脚本执行
}

// 从会话中获取用户信息
$user = $_SESSION['user'];
$username = $user['username'];

// 处理重新上传头像
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['avatar'])) {
    $userDir = 'uploads_avatar/' . $username . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0777, true);
    }

    $tmp_name = $_FILES['avatar']['tmp_name'];
    $originalName = $_FILES['avatar']['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);

    // 找到新的编号
    $index = 0;
    while (file_exists($userDir . $username . '_avatar_' . $index . '.' . $ext)) {
        $index++;
    }
    $newName = $username . '_avatar_' . $index . '.' . $ext;
    $avatarPath = $userDir . $newName;

    move_uploaded_file($tmp_name, $avatarPath);

    // 更新数据库中最新的头像路径
    $sql = "UPDATE users SET avatar = '$avatarPath' WHERE id = " . $user['id'];
    if ($conn->query($sql) === TRUE) {
        // 同时更新 session
        $_SESSION['user']['avatar'] = $avatarPath;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "上传失败: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户中心</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-box">
        <h2>欢迎，<?php echo htmlspecialchars($user['username']); ?></h2>

        <h3>当前头像：</h3>
        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="头像" width="150"><br>

        <!-- 重新上传头像 -->
        <h3>上传新头像</h3>
        <form action="dashboard.php" method="post" enctype="multipart/form-data">
            <input type="file" name="avatar" required>
            <button type="submit">上传</button>
        </form>

        <!-- 列出历史所有头像 -->
        <h3>历史头像</h3>
        <?php
        $dir = 'uploads_avatar/' . $username . '/';
        $files = glob($dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        foreach ($files as $file) {
            echo '<img src="' . htmlspecialchars($file) . '" width="100" style="margin:5px;">';
        }
        ?>

        <br><br>
        <a href="logout.php">退出登录</a>
    </div>
</body>
</html>