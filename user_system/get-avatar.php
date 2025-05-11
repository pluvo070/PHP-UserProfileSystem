<?php
// get-avatar.php - 安全输出用户头像

include __DIR__ . '/admin/config.php';
include __DIR__ . '/admin/init.php'; 

function sendImage($path) {
    if (!file_exists($path) || !is_file($path)) {
        http_response_code(404);
        exit("头像文件不存在");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path);
    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'])) {
        http_response_code(403);
        exit("不允许的图片类型");
    }

    header("Content-Type: $mime");
    header("Content-Length: " . filesize($path));
    readfile($path);
    exit;
}

$username = $_GET['user'] ?? ($_SESSION['user']['username'] ?? null);
if (!$username) {
    http_response_code(401);
    exit("未登录或未指定用户");
}

// 获取头像路径
if (isset($_GET['file'])) { // 已经指定了历史头像文件名
    $safeFilename = basename($_GET['file']); // 防止路径穿越
    $avatarPath = __DIR__ . DIRECTORY_SEPARATOR. "uploads_avatar". DIRECTORY_SEPARATOR. "$username". DIRECTORY_SEPARATOR. "$safeFilename";
    $avatarPath = realpath(__DIR__ . "/uploads_avatar/$username/$safeFilename");
    // 验证路径是否还在用户的目录内(防止绕过 basename)
    $userDir = realpath(__DIR__ . "/uploads_avatar/$username/");
    if (!$avatarPath || strpos($avatarPath, $userDir) !== 0) {
        http_response_code(403);
        exit("非法文件访问");
    }
    sendImage($avatarPath);
} else {
    // 查询数据库中的最新头像路径
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $relativePath = $row['avatar'];
        $avatarPath = realpath(__DIR__ . '/' . $relativePath);
        if (!$avatarPath || strpos($avatarPath, realpath(__DIR__ . '/uploads_avatar/')) !== 0) {
            http_response_code(403);
            exit("头像路径无效");
        }
        sendImage($avatarPath);
    } else {
        //echo "调试：头像路径不存在 -> " . $avatarPath;
        http_response_code(404);
        exit("用户不存在");
    }
}
