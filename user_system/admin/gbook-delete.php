<?php

// 后端处理删除请求

header('Content-Type: application/json');// 设置响应的内容类型为 JSON
include __DIR__ . '/config.php';
include __DIR__ . '/init.php';

// 获取请求中的操作类型，默认为空字符串
$action = $_POST['action'] ?? '';
$response = ['success' => false]; // 初始化响应数组，默认为操作失败

// 判断操作类型: 删除一条还是删除该用户全部
if ($action === 'delete_one') {
    // 删除单条留言
    $id = intval($_POST['id']); // 获取留言 ID，确保为整数
    // $sql = "DELETE FROM gbook WHERE id = $id"; // 构造删除 SQL 语句
    // if ($conn->query($sql)) {
    //     $response = ['success' => true, 'message' => '该条留言已删除'];
    // } else {
    //     $response['error'] = mysqli_error($conn);
    // }
    $stmt = $conn->prepare("DELETE FROM gbook WHERE id = ?"); // 使用预处理语句
    $stmt->bind_param("i", $id); // 绑定参数
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => '该条留言已删除'];
    } else {
        $response['error'] = $stmt->error;
    }
    $stmt->close(); // 关闭语句
} elseif ($action === 'delete_all') {
    // 删除该用户的所有留言
    // $username = mysqli_real_escape_string($conn, $_POST['username']); // 获取用户名并防止 SQL 注入
    // $sql = "DELETE FROM gbook WHERE username = '$username'";
    // if ($conn->query($sql)) {
    //     $response = ['success' => true, 'message' => '该用户所有留言已删除'];
    // } else {
    //     $response['error'] = mysqli_error($conn);
    // }
    $username = $_POST['username'] ?? ''; // 获取用户名
    $stmt = $conn->prepare("DELETE FROM gbook WHERE username = ?"); // 使用预处理语句
    $stmt->bind_param("s", $username); // 绑定参数
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => '该用户所有留言已删除'];
    } else {
        $response['error'] = $stmt->error;
    }
    $stmt->close(); // 关闭语句
} else {
    // 操作类型无效
    $response['error'] = '无效操作';
}

$conn->close();
echo json_encode($response); // 把响应结果返回给前端 JS 
