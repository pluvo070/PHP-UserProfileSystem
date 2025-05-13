<?php

// 用于处理用户添加一条留言内容的逻辑, 不展示为单独的一个页面


include __DIR__ . '/admin/config.php'; // 连接数据库
include __DIR__ . '/admin/init.php'; // 初始化会话和token

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 引入 HTMLPurifier 库(XSS 过滤器)
require_once __DIR__ . '/../lib/HTMLPurifier/library/HTMLPurifier.auto.php'; 

$user = $_SESSION['user'];
$username = $user['username'];
$user_id = $user['id']; // 使用用户ID作为外键
// 获取内容并写入数据库
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $rawContent = trim($_POST['content']);

    // 过滤 HTML 内容，防止 XSS 攻击
    $purifierConfig = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifierConfig);
    $content = $purifier->purify($rawContent);

    if (empty($content)) {
        die("内容不能为空！");
    }

    $ipaddr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $uagent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("INSERT INTO user_blogs (user_id, content, ipaddr, uagent) VALUES (?, ?, ?, ?)");

    if ($stmt === false) {
        die("SQL 错误: " . $conn->error);
    }

    $stmt->bind_param("isss", $user_id, $content, $ipaddr, $uagent);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // 可跳转到博客列表页或主页
        exit();
    } else {
        echo "发表失败：" . $conn->error;
    }

    $stmt->close();
} else {
    echo "非法请求。";
}
?>
