<?php
require_once 'TokenAuth.php';
require_once 'config.php';

header('Content-Type: application/json');

try {
    // 验证session
    if (!TokenAuth::verifySession()) {
        throw new Exception('请先使用token认证', 401);
    }

    // 验证上传类型
    $uploadType = $_POST['upload_type'] ?? '';
    if (!in_array($uploadType, ['pc', 'phone'])) {
        throw new Exception('无效的上传类型');
    }

    // 处理批量上传
    if (!isset($_FILES['files'])) {
        throw new Exception('没有文件被上传');
    }

    $files = $_FILES['files'];
    $results = [];
    $basePath = '../api/';
    $uploadDir = [
        'pc' => $basePath . 'pc/',
        'phone' => $basePath . 'phone/'
    ];

    // 创建目录
    foreach ($uploadDir as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $results[] = [
                'success' => false,
                'filename' => $files['name'][$i],
                'message' => '上传失败'
            ];
            continue;
        }

        // 验证文件大小
        if ($files['size'][$i] > Config::MAX_FILE_SIZE) {
            $results[] = [
                'success' => false,
                'filename' => $files['name'][$i],
                'message' => '文件大小超过限制'
            ];
            continue;
        }

        // 创建临时文件
        $tempFile = tempnam('/tmp', 'upload_');
        move_uploaded_file($files['tmp_name'][$i], $tempFile);

        // 验证文件类型
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tempFile);
        if (!in_array($mime, Config::ALLOWED_TYPES)) {
            unlink($tempFile);
            $results[] = [
                'success' => false,
                'filename' => $files['name'][$i],
                'message' => '不支持的文件类型'
            ];
            continue;
        }

        // 生成唯一文件名
        $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $fileHash = md5_file($tempFile);
        $safeName = 'pl_' . $fileHash . '_' . time() . '_' . $i . '.' . $extension;
        $targetPath = $uploadDir[$uploadType] . $safeName;

        // 保存文件
        if (rename($tempFile, $targetPath)) {
            // 记录到数据库 - 使用UTC+8时间
            $db = Config::getDB();
            $currentTime = TokenAuth::getCurrentCSTTime();
            
            $stmt = $db->prepare("
                INSERT INTO uploaded_files 
                (original_name, stored_name, file_size, file_type, upload_type, upload_ip, token_used, uploaded_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $files['name'][$i],
                $safeName,
                $files['size'][$i],
                $mime,
                $uploadType,
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['token'],
                $currentTime
            ]);
        
            $results[] = [
                'success' => true,
                'filename' => $files['name'][$i],
                'stored_name' => $safeName,
                'file_size' => $files['size'][$i],
                'file_type' => $mime,
                'upload_time' => $currentTime,
                'message' => '上传成功'
            ];
        } else {
            unlink($tempFile);
            $results[] = [
                'success' => false,
                'filename' => $files['name'][$i],
                'message' => '文件保存失败'
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    $code = $e->getCode();
    http_response_code(is_int($code) && $code > 0 ? $code : 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>