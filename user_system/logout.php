<?php
session_start();
session_unset();
session_destroy(); // 删除服务器端的session文件
header("Location: index.php");
exit();
?>