<?php

// 用户登录后的面板页面

include __DIR__ . '/admin/config.php'; // 连接数据库
include __DIR__ . '/admin/init.php'; // 初始化会话和token


// 检查用户是否已登录($_SESSION的user键已经有值)
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // 如果用户未登录，重定向到登录页面
    exit(); // 终止该脚本执行
}

// 从会话中获取用户信息
$user = $_SESSION['user'];
$username = $user['username'];
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户中心</title>
    <link rel="stylesheet" href="style.css">

    <!-- 引入 UEditor -->
    <script type="text/javascript" src="../lib/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="../lib/ueditor/ueditor.all.min.js"></script>
    <script type="text/javascript" src="../lib/ueditor/lang/zh-cn/zh-cn.js"></script>
    <script>
        UE.getEditor('gbook-editor');
    </script>

</head>
<body>
    <div class="dashboard-box">
        <h2>欢迎，<?php echo htmlspecialchars($user['username']); ?></h2>

        <h3>当前头像</h3>
        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="头像" id="avatarnow"><br>

        <!-- 上传新头像 -->
        <h3>上传新头像</h3>
        <form enctype="multipart/form-data" id="avatar-upload-form"> <!--删除:action="avatar-upload.php" method="post"-->
            <!-- 这个隐藏字段用于保存token, 便于在 JS 中取用 -->
            <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="file" name="avatar" required id="avatar-input">
            <button type="submit" id="avatar-upload-button">上传</button>
        </form>
        <p id="upload-result" style="color: red;"></p> <!-- 如果上传了错误类型文件,则显示红色提示 -->

        <!-- 列出历史所有头像 -->
        <h3>历史头像</h3>
        <div id="avatar-gallery">
            <?php
            $dir = 'uploads_avatar/' . $username . '/';
            $files = glob($dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            // 使用 filemtime 进行时间倒序排序(最新头像在最前面)
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
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

            <!-- 留言表单 -->
            <form action="gbook.php" method="post" id="gbook-form">
                <?php csrf_input_field(); ?><!-- 发送隐藏的token -->
                <script id="gbook-editor" name="content" type="text/plain" style="width:100%;height:200px;"></script>
                <button type="submit">发布留言</button>
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
                        echo "  <em>" . $row['created_at'] . "</em><br>";
                        // 显示留言内容，因为使用了 HTMLPurifier 所以可以直接输出内容而不考虑 XSS 攻击
                        echo "<p>" . $row['content'] . "</p>";
                        // 显示留言者的 IP 地址和浏览器信息
                        echo "<small>IP: " . htmlspecialchars($row['ipaddr']) . " | From: " . htmlspecialchars($row['uagent']) . "</small>";
                        echo "</div>";
                    }
                } else {
                    echo "暂无留言。";
                }
                ?>
            </div>
        </div>
    </div>


    <!-- 异步上传 JS: 用于重新上传头像-->
    <script>
    document.getElementById('avatar-upload-form').addEventListener('submit', function(e) {
        e.preventDefault(); // 阻止默认提交行为
        const formData = new FormData(this);

        // 加入 CSRF Token(从html中的隐藏字段中取)
        formData.append('csrf_token', document.getElementById('csrf_token').value);

        fetch('avatar-upload.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                window.location.reload(); // 成功后刷新页面
            } else {
                // upload-result 是错误信息 HTML 元素的 ID
                document.getElementById('upload-result').textContent = result.error;
            }
        })
        .catch(() => {
            document.getElementById('upload-result').textContent = "上传失败，请稍后再试。";
        });
    });
    </script>



</body>
</html>