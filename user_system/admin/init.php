<?php

// 全局会话管理脚本, 包含 CSRF Token 相关函数

// 启动 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 初始化 Token (每个会话生成一次)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 输出隐藏的 Token 表单字段
function csrf_input_field() {
    $token = $_SESSION['csrf_token'] ?? '';
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// 全局验证 POST 请求中的 Token: 所有 POST 接口都必须提交 token, 否则都会被拦截
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_post = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_token'] ?? '';
    if ($token_post !== $token_session) {
        // AJAX 请求可以返回 JSON 错误信息
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'error' => '非法请求, token 验证失败']));
    }
}

