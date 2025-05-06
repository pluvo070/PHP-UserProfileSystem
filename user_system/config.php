<?php

// 用于连接数据库: 面向对象风格, 使用 mysqli 类
$servername = "localhost";

// $username = "...";          // 本地MySQL的用户名
// $password = "...";          // 密码
// $dbname = "user_system";    // 数据库名


/* 使用.env配置文件保护密码(需要Composer环境) */

// 1. 引入Composer安装的库
require_once __DIR__ . '/../vendor/autoload.php'; 

// 2. 找到当前目录里的.env文件, 其中__DIR__ 是一个魔术常量, 返回当前脚本所在的目录 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 

// 3. 把.env文件中写的内容加载到 PHP 的环境变量中                  
$dotenv->load(); 

/* 创建数据库连接 */

$servername = $_ENV['DB_HOST']; // 等于 localhost
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME']; // 等于 user_system

// 使用面向对象的方法创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

?>
