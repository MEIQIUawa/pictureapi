<?php
// 处理预检OPTIONS请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

// 配置文件设置
$maxFileSize = 50 * 1024 * 1024;
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/bmp'];
$basePath = './';
$uploadDir = [
    'pc' => $basePath.'api/pc/',
    'phone' => $basePath.'api/phone/',
    'invalid' => $basePath.'api/no/'
];

// 创建目录（如果不存在）
foreach ($uploadDir as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

try {
    // 验证上传类型
    $uploadType = $_POST['upload_type'] ?? '';
    if (!in_array($uploadType, ['pc', 'phone'])) {
        throw new Exception('无效的上传类型');
    }

    // 验证文件上传
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('文件上传失败');
    }

    $file = $_FILES['file'];

    // 验证文件大小
    if ($file['size'] > $maxFileSize) {
        throw new Exception('文件大小超过50MB限制');
    }

    // 创建安全临时文件
    $tempFile = tempnam('/tmp', 'upload_');
    if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
        throw new Exception('临时文件移动失败');
    }

    // 验证文件类型
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tempFile);
    if (!in_array($mime, $allowedTypes)) {
        $invalidName = uniqid('invalid_', true).'_'.basename($file['name']);
        rename($tempFile, $uploadDir['invalid'].$invalidName);
        throw new Exception('仅支持图片文件（JPEG/PNG/WEBP/GIF）');
    }

    // 生成安全文件名（如果文件已存在，添加后缀）
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileHash = md5_file($tempFile);
    $baseName = $fileHash;
    $safeName = $baseName . '.' . $extension;
    $targetPath = $uploadDir[$uploadType] . $safeName;
    
    // 检查文件是否已存在，如果存在则添加数字后缀
    $counter = 1;
    while (file_exists($targetPath)) {
        $safeName = $baseName . '_' . $counter . '.' . $extension;
        $targetPath = $uploadDir[$uploadType] . $safeName;
        $counter++;
    }

    // 移动文件到目标目录
    if (!rename($tempFile, $targetPath)) {
        throw new Exception('文件保存失败');
    }

    // 返回成功响应
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'filepath' => $targetPath,
        'filename' => $safeName,
        'message' => '上传成功'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode() ?: 400
    ]);
    
} finally {
    // 确保清理临时文件
    if (isset($tempFile) && file_exists($tempFile)) {
        @unlink($tempFile);
    }
}
?>