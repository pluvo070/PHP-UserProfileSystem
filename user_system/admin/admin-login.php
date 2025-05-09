<?php
// admin-login.php
session_start();
include __DIR__ . '/config.php';

// 如果已登录管理员，直接跳转后台主页
if (isset($_SESSION['admin'])) {
    header("Location: admin-dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "请输入用户名和密码";
    } else {
        // 查询数据库中是否存在管理员用户
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1 LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin'] = $user;
                header("Location: admin-dashboard.php");
                exit();
            } else {
                $error = "密码错误";
            }
        } else {
            $error = "不存在该管理员或无权限";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员登录</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>管理员登录</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label>用户名：<input type="text" name="username" required></label><br>
        <label>密码：<input type="password" name="password" required></label><br>
        <button type="submit">登录</button>
    </form>
</body>
</html>
