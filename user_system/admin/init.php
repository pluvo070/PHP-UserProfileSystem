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
        // 判断是否是 Ajax 请求通常通过 XMLHttpRequest 发送
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'error' => '非法请求，token 验证失败']));
        } else {
            // 普通表单请求，直接终止脚本执行并返回错误提示
            die('非法请求，CSRF token 验证失败。');
        }
    }
}

