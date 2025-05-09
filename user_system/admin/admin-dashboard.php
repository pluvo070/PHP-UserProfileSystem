<?php
// 管理留言板的页面, 删除操作逻辑在 gbook-delete.php
include __DIR__ . '/config.php';
$sql = "SELECT * FROM gbook ORDER BY id DESC";
$result = $conn->query($sql); 
?>

<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>留言管理</title> 
        <link rel="stylesheet" href="../style.css">
        <!-- 内联式 JS 处理异步请求 -->
        <script> 
        // 定义删除留言的函数
        function deleteMessage(id, all = false, username = '') {
            let formData = new FormData();
            // 添加数据到 formData, 用于后续发送键值对数据给删除操作处理文件 gbook-delete.php
            formData.append('action', all ? 'delete_all' : 'delete_one'); // 根据第二个参数决定删除单条留言还是所有留言
            formData.append('id', id);
            formData.append('username', username);
            // 使用 Fetch API 发送 POST 请求到 gbook-delete.php
            fetch('gbook-delete.php', {
                method: 'POST',
                body: formData // 将 FormData 作为请求体发送
            })
            // 接收 fetch 请求返回的响应, 并解析为 JSON 格式
            .then(response => response.json()) 
            // 解析第一个 then 返回的数据(JSON 响应数据), 从中提取响应结果信息, 显示在页面上
            .then(responsejson => {
                // 根据返回结果显示提示
                if (responsejson.success) {
                    alert(responsejson.message);
                    location.reload(); // 刷新页面以更新留言列表
                } else {
                    alert('删除失败: ' + responsejson.error);
                }
            });
        }
        </script>
    </head>
    <body>
        <div class="dashboard-box">
            <h1 style="text-align: center;">管理员面板</h1>
            <h2 style="color: #BA6CA8;">留言管理</h2>
            <?php
            // 遍历每一条查询记录, 对每条留言生成 HTML 元素，包括两个删除按钮
            while ($row = mysqli_fetch_assoc($result)) { 
                echo "<div class='message'>";
                if (!empty($row['avatar'])) {
                    echo "<img src='" . htmlspecialchars($row['avatar']) . "' class='gbook-avatar'>";
                }
                echo "<strong>" . htmlspecialchars($row['username']) . "</strong>";
                echo " <em>" . $row['created_at'] . "</em><br>";
                echo "<p>" . $row['content'] . "</p>";
                echo "<small>IP: " . htmlspecialchars($row['ipaddr']) . " | From: " . htmlspecialchars($row['uagent']) . "</small>";
                echo "<br><br>";
                /* 在按钮的 onclick 事件中调用 JS 中定义的函数 deleteMessage，并将留言的 ID 传递给它
                第二个参数为 true 时表示删除该用户所有留言, 在这个情况下才传递第三个参数用户名 */
                echo "<button onclick=\"deleteMessage(" . $row['id'] . ", false)\">删除该条留言</button> ";
                echo "<button onclick=\"deleteMessage(0, true, '" . htmlspecialchars($row['username']) . "')\">删除该用户所有留言</button>";
                echo "</div><hr>";
            }
            ?>
        </div>
    </body>
</html>
