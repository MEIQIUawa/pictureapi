<?php
require_once 'TokenAuth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';
    
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Token不能为空']);
        exit;
    }
    
    if (TokenAuth::validateToken($token)) {
        echo json_encode(['success' => true, 'message' => '认证成功']);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token无效或已过期']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '方法不允许']);
}
?>