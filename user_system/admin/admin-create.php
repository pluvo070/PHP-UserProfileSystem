<?php

// 用于一次性地生成一个管理员账号密码(使用后需要删除该文件)
// 这是因为 password_hash() 每次生成的哈希都不同，不能直接手写加密后的密码进数据库

include __DIR__ . '/config.php';

$username = 'admin';
$password_plain = 'admin';
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
$email = 'admin@admin.com';

// 插入管理员账号，is_admin 字段标记为 1
$stmt = $conn->prepare("INSERT INTO users (username, password, email, avatar, is_admin) VALUES (?, ?, ?, '', 1)");
$stmt->bind_param("sss", $username, $password_hashed, $email);

if ($stmt->execute()) {
    echo "管理员账号创建成功，请删除此文件！";
} else {
    echo "创建失败：" . $stmt->error;
}

$stmt->close();
$conn->close();
?>
