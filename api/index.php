<?php
$counterFile = './num.txt';

// 获取访问次数
function getVisits($file) {
    if (!file_exists($file)) {
        file_put_contents($file, '0');
    }
    return intval(file_get_contents($file));
}

function getRandomImage($directory) {
    $images = glob($directory . '/*.{jpg,jpeg,png,gif,bmp}', GLOB_BRACE);
    if (!$images) {
        return null;
    }
    $randomImage = $images[array_rand($images)];
    return $randomImage;
}

function outputGif($gifPath) {
    header("Content-Type: image/gif");
    readfile($gifPath);
    exit;
}

function outputImage($imagePath) {
    if (!$imagePath) {
        header("HTTP/1.0 404 Not Found");
        exit;
    }

    $imageInfo = getimagesize($imagePath);
    header("Content-Type: " . $imageInfo['mime']);
    readfile($imagePath);
    exit;
}

$requestUri = $_SERVER['REQUEST_URI'];
$queryParams = [];
parse_str(parse_url($requestUri, PHP_URL_QUERY), $queryParams);

$equ = $queryParams['equ'] ?? 'pc';

if ($equ === 'pc') {
    $imagePath = getRandomImage('apipc');
    outputImage($imagePath);
} elseif ($equ === 'phone') {
    $imagePath = getRandomImage('apiphone');
    outputImage($imagePath);
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "Invalid 'equ' parameter.";
}

// 每次访问增加计数（可选）
file_put_contents($counterFile, getVisits($counterFile) + 1);
?>