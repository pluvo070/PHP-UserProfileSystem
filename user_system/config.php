<?php
// 用于连接数据库: 面向对象风格, 使用 mysqli 类
$servername = "localhost";
// $username = "admin";     // PHPStudy的MySQL服务默认用户名
$username = "root";         // 本地MySQL的默认用户名
$password = "123456";       // 默认密码
$dbname = "user_system";    // 数据库名

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
?>
