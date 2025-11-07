<?php
require_once 'TokenAuth.php';
require_once 'config.php';

header('Content-Type: application/json');

try {
    if (!TokenAuth::verifySession()) {
        throw new Exception('请先认证', 401);
    }

    $db = Config::getDB();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 获取文件列表
        $stmt = $db->prepare("
            SELECT * FROM uploaded_files 
            WHERE token_used = ? 
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute([$_SESSION['token']]);
        $files = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'files' => $files]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // 删除文件
        $data = json_decode(file_get_contents('php://input'), true);
        $fileId = $data['file_id'] ?? 0;
        
        // 获取文件信息
        $stmt = $db->prepare("
            SELECT * FROM uploaded_files 
            WHERE id = ? AND token_used = ?
        ");
        $stmt->execute([$fileId, $_SESSION['token']]);
        $file = $stmt->fetch();
        
        if (!$file) {
            throw new Exception('文件不存在或无权删除');
        }
        
        // 删除物理文件
        $filePath = '../api/' . $file['upload_type'] . '/' . $file['stored_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // 删除数据库记录
        $deleteStmt = $db->prepare("DELETE FROM uploaded_files WHERE id = ?");
        $deleteStmt->execute([$fileId]);
        
        echo json_encode(['success' => true, 'message' => '删除成功']);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>