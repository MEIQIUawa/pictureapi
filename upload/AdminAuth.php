<?php
require_once 'config.php';

class AdminAuth {
    // 获取客户端IP地址
    private static function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }
    
    // 检查IP是否被封禁
    private static function isIPBlocked($ip) {
        $db = Config::getDB();
        
        // 获取最近10分钟内该IP的错误登录尝试次数
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempt_count 
            FROM login_attempts 
            WHERE ip_address = ? 
            AND success = 0 
            AND attempt_time > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        return $result['attempt_count'] >= 3; // 超过3次错误则封禁
    }
    
    // 记录登录尝试
    private static function recordLoginAttempt($ip, $username, $success) {
        $db = Config::getDB();
        
        // 将布尔值转换为整数（1表示TRUE，0表示FALSE）
        $success_int = $success ? 1 : 0;
        
        $stmt = $db->prepare("
            INSERT INTO login_attempts (ip_address, username, success) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$ip, $username, $success_int]);
        
        // 如果登录成功，清除该IP的错误计数
        if ($success) {
            $stmt = $db->prepare("
                DELETE FROM login_attempts 
                WHERE ip_address = ? AND success = 0
            ");
            $stmt->execute([$ip]);
        }
    }
    
    public static function login($username, $password) {
        $db = Config::getDB();
        $clientIP = self::getClientIP();
        
        // 检查IP是否被封禁
        if (self::isIPBlocked($clientIP)) {
            self::recordLoginAttempt($clientIP, $username, false);
            return ['success' => false, 'message' => 'IP地址已被封禁10分钟，请稍后再试'];
        }
        
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_expire'] = time() + (60 * 60); // 1小时有效期
            
            // 记录成功登录
            self::recordLoginAttempt($clientIP, $username, true);
            
            return ['success' => true, 'message' => '登录成功'];
        }
        
        // 记录失败登录
        self::recordLoginAttempt($clientIP, $username, false);
        
        // 检查当前错误次数
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempt_count 
            FROM login_attempts 
            WHERE ip_address = ? 
            AND success = 0 
            AND attempt_time > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $stmt->execute([$clientIP]);
        $result = $stmt->fetch();
        
        $remaining_attempts = 3 - $result['attempt_count'];
        
        if ($remaining_attempts <= 0) {
            return ['success' => false, 'message' => 'IP地址已被封禁10分钟，请稍后再试'];
        } else {
            return ['success' => false, 'message' => '用户名或密码错误，剩余尝试次数: ' . $remaining_attempts];
        }
    }
    
    public static function isLoggedIn() {
        session_start();
        
        return isset($_SESSION['admin_logged_in'], 
                    $_SESSION['admin_username'], 
                    $_SESSION['admin_expire']) && 
               $_SESSION['admin_expire'] > time();
    }
    
    public static function logout() {
        session_start();
        session_destroy();
    }
    
    public static function getCurrentAdmin() {
        session_start();
        return $_SESSION['admin_username'] ?? null;
    }
}
?>