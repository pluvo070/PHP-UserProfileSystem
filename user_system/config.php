<?php
// 用于连接数据库

$servername = "localhost";
$username = "admin";   // 默认用户名
$password = "123456";       // 默认密码
$dbname = "user_system";  // 数据库名

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
?>
