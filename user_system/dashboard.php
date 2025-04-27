<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>

<h1>欢迎, <?php echo htmlspecialchars($user['username']); ?></h1>
<p>邮箱: <?php echo htmlspecialchars($user['email']); ?></p>
<p><img src="<?php echo $user['avatar']; ?>" alt="Avatar" width="100"></p>
<a href="logout.php">退出</a>
