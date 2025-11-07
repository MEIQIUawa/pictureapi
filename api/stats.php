<?php
header('Content-Type: application/json');

// 配置路径
$pcDir = './apipc';
$phoneDir = './apiphone';
$counterFile = './num.txt';

// 获取壁纸数量
function countFiles($dir) {
    return count(glob(rtrim($dir, '/').'/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE));
}

// 获取访问次数
function getVisits($file) {
    if (!file_exists($file)) {
        file_put_contents($file, '0');
    }
    return intval(file_get_contents($file));
}

try {
    // 返回JSON数据
    echo json_encode([
        'pc' => countFiles($pcDir),
        'phone' => countFiles($phoneDir),
        'visits' => getVisits($counterFile)
    ]);
    
    // 每次访问增加计数（可选）
    // file_put_contents($counterFile, getVisits($counterFile) + 1);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '服务器内部错误']);
}
?>