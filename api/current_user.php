<?php
// 当前登录用户信息存储文件（保持向后兼容）
$current_user_file = __DIR__ . '/current_user.txt';
$current_user_time_file = __DIR__ . '/current_user_time.txt';

// 包含upload目录中的数据库配置和AdminAuth类
require_once '../upload/config.php';
require_once '../upload/AdminAuth.php';

// 获取当前登录用户（优先使用数据库验证）
function getCurrentUser() {
    // 首先检查数据库登录状态
    if (AdminAuth::isLoggedIn()) {
        return AdminAuth::getCurrentAdmin();
    }
    
    // 如果数据库登录无效，回退到文件存储方式
    global $current_user_file;
    if (file_exists($current_user_file)) {
        return trim(file_get_contents($current_user_file));
    }
    return '';
}

// 获取当前登录用户的时间戳
function getCurrentUserTime() {
    // 如果使用数据库登录，返回当前时间（数据库会话已包含超时管理）
    if (AdminAuth::isLoggedIn()) {
        return time();
    }
    
    // 回退到文件存储方式
    global $current_user_time_file;
    if (file_exists($current_user_time_file)) {
        return (int)trim(file_get_contents($current_user_time_file));
    }
    return 0;
}

// 设置当前登录用户
function setCurrentUser($username) {
    // 对于数据库登录，用户信息已通过AdminAuth管理
    // 这里保持文件存储方式以保持向后兼容
    global $current_user_file, $current_user_time_file;
    file_put_contents($current_user_file, $username);
    file_put_contents($current_user_time_file, time()); // 存储当前时间戳
}

// 更新当前用户活动时间
function updateCurrentUserTime() {
    // 对于数据库登录，活动时间由AdminAuth自动管理
    // 这里更新文件存储的时间戳
    global $current_user_time_file;
    if (file_exists($current_user_time_file)) {
        file_put_contents($current_user_time_file, time());
    }
}

// 清除当前登录用户
function clearCurrentUser() {
    // 清除数据库登录会话
    session_start();
    if (isset($_SESSION['admin_logged_in'])) {
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_expire']);
    }
    
    // 清除文件存储的用户信息
    global $current_user_file, $current_user_time_file;
    if (file_exists($current_user_file)) {
        unlink($current_user_file);
    }
    if (file_exists($current_user_time_file)) {
        unlink($current_user_time_file);
    }
}

// 检查会话是否超时（10分钟）
function checkSessionTimeout() {
    // 首先检查数据库会话超时
    if (!AdminAuth::isLoggedIn()) {
        // 数据库会话已过期，清除文件存储的用户信息
        clearCurrentUser();
        return true;
    }
    
    // 然后检查文件存储的会话超时
    $last_activity = getCurrentUserTime();
    if ($last_activity > 0 && (time() - $last_activity) > 600) { // 10分钟 = 600秒
        // 会话超时，清除用户信息
        clearCurrentUser();
        return true;
    }
    
    return false;
}
?>