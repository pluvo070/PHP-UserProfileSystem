<?php

// 开启php会话功能
session_start();

// 连接数据库
include 'config.php';

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

        <h3>当前头像</h3>
        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="头像" id="avatarnow"><br>

        <!-- 上传新头像 -->
        <h3>上传新头像</h3>
        <form action="dashboard.php" method="post" enctype="multipart/form-data" id="avatar-upload-form">
            <input type="file" name="avatar" required id="avatar-input">
            <button type="submit" id="avatar-upload-button">上传</button>
        </form>

        <!-- 列出历史所有头像 -->
        <h3>历史头像</h3>
        <div id="avatar-gallery">
            <?php
            $dir = 'uploads_avatar/' . $username . '/';
            $files = glob($dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            foreach ($files as $file) {
                echo '<img src="' . htmlspecialchars($file) . ' "class="avatar-thumbnail"> ';
            }
            ?>
        </div>
        <br><br>
        <a href="logout.php">退出登录</a>
    </div>


    <hr>
    <div class="gbook-box">
        <h3>世界留言板</h3>
        <form action="gbook.php" method="post">
            <textarea name="content" rows="4" cols="50" placeholder="说点什么..." required></textarea><br>
            <button type="submit">发布留言</button>
        </form>
        <hr>
        <div class="gbook-messages">
            <h4>留言列表：</h4>
            <?php
            // 查询留言记录
            $sql = "SELECT username, content, ipaddr, uagent, created_at FROM gbook ORDER BY created_at DESC";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='message'>";
                    echo "<strong>" . htmlspecialchars($row['username']) . "</strong> 留言于 <em>" . $row['created_at'] . "</em><br>";
                    echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
                    echo "<small>IP: " . htmlspecialchars($row['ipaddr']) . " | 浏览器: " . htmlspecialchars($row['uagent']) . "</small>";
                    echo "</div><hr>";
                }
            } else {
                echo "暂无留言。";
            }
            ?>
        </div>
    </div>

</body>
</html>