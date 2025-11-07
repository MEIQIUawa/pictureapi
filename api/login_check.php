<?php
header('Content-Type: application/json');

// 包含数据库配置和AdminAuth类
require_once '../upload/config.php';
require_once '../upload/AdminAuth.php';

// 检查当前登录状态
function checkLoginStatus() {
    if (AdminAuth::isLoggedIn()) {
        return [
            'code' => 200,
            'msg' => '已登录',
            'data' => [
                'username' => AdminAuth::getCurrentAdmin(),
                'logged_in' => true
            ]
        ];
    } else {
        return [
            'code' => 401,
            'msg' => '未登录',
            'data' => [
                'logged_in' => false
            ]
        ];
    }
}

// 处理请求
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = checkLoginStatus();
    echo json_encode($result);
} else {
    echo json_encode(['code' => 405, 'msg' => '不支持的请求方法']);
}
?>