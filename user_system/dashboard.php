<?php

// 开启php会话功能
session_start();

// 连接数据库
include 'admin/config.php';

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

        <!-- 留言板部分 -->
        <hr>
        <div class="gbook-box">
            <h3>世界留言板</h3>
            <form action="gbook.php" method="post" id="gbook-form">
                <textarea name="content" id="gbook-textarea" rows="4" cols="50" placeholder="说点什么..." required></textarea><br>
                <button type="submit" id="gbook-submit">发布留言</button>
            </form>
            <hr>
            <div class="gbook-messages">
                <h4>留言列表：</h4>
                <?php
                // 查询留言记录, 并联合users表获得该用户的最新头像路径
                $sql = "
                    SELECT gbook.username, gbook.content, gbook.ipaddr, gbook.uagent, gbook.created_at, users.avatar
                    FROM gbook
                    LEFT JOIN users ON gbook.username = users.username
                    ORDER BY gbook.created_at DESC
                ";
                $result = $conn->query($sql); // 获取所有查询结果
                if ($result && $result->num_rows > 0) { 
                    while ($row = $result->fetch_assoc()) { // 遍历结果的每一行
                        echo "<div class='message'>";
                        // 头像
                        if (!empty($row['avatar'])) {
                            echo "<img src='" . htmlspecialchars($row['avatar']) . "' class='gbook-avatar'>";
                        }
                        // 显示留言者用户名和留言时间
                        echo "<strong>" . htmlspecialchars($row['username']) . "</strong>";
                        echo " <em>" . $row['created_at'] . "</em><br>";
                        // 显示留言内容，nl2br 用于将换行符转换为 <br> 标签
                        echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
                        // 显示留言者的 IP 地址和浏览器信息
                        echo "<small>IP: " . htmlspecialchars($row['ipaddr']) . " | From: " . htmlspecialchars($row['uagent']) . "</small>";
                        echo "</div><hr>";
                    }
                } else {
                    echo "暂无留言。";
                }
                ?>
            </div>
        </div>


    </div>


</body>
</html>