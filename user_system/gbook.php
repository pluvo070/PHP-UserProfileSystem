<?php

// 用于处理用户添加一条留言内容的逻辑, 不展示为单独的一个页面


include __DIR__ . '/admin/config.php'; // 连接数据库
include __DIR__ . '/admin/init.php'; // 初始化会话和token


// 确保用户已登录
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 引入 HTMLPurifier 库(XSS 过滤器)
require_once __DIR__ . '/../lib/HTMLPurifier/library/HTMLPurifier.auto.php'; 

$user = $_SESSION['user'];
$username = $user['username'];

// 获取留言内容
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    // $content = trim($_POST['content']);

    // 过滤 HTML 内容，防止 XSS 攻击
    $purifierConfig = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifierConfig);
    $rawContent = trim($_POST['content']);
    $content = $purifier->purify($rawContent);  // 执行过滤

    if (empty($content)) {
        die("留言内容不能为空！");
    }

    // 获取用户IP和UA, 自动存储到数据库中
    $ipaddr = $_SERVER['REMOTE_ADDR'];
    $uagent = $_SERVER['HTTP_USER_AGENT'];

    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("INSERT INTO gbook (username, content, ipaddr, uagent, created_at) VALUES (?, ?, ?, ?, NOW())");

    if ($stmt === false) { // 检查 prepare 是否成功
        die("SQL 错误: " . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $content, $ipaddr, $uagent); // 绑定参数

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "留言失败：" . $conn->error;
    }

    $stmt->close();
} else {
    echo "非法请求。";
}
?>
