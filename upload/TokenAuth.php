<?php
// 确保先加载配置文件
require_once __DIR__ . '/config.php';

class TokenAuth {
    public static function validateToken($token) {
        $db = Config::getDB();
        $clientIP = self::getClientIP();
        
        // 清理过期token
        self::cleanExpiredTokens();
        
        // 使用PHP时间进行比较，避免数据库时区问题
        $currentTime = self::getCurrentCSTTime();
        
        $stmt = $db->prepare("
            SELECT * FROM tokens 
            WHERE token = ? AND is_used = FALSE
        ");
        $stmt->execute([$token]);
        
        if ($tokenData = $stmt->fetch()) {
            // 在PHP中检查是否过期
            if (strtotime($tokenData['expires_at']) < strtotime($currentTime)) {
                // 标记为已过期
                $updateStmt = $db->prepare("
                    UPDATE tokens SET is_used = TRUE, used_by_ip = ?, used_at = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$clientIP, $currentTime, $tokenData['id']]);
                return false;
            }
            
            // 标记token为已使用，记录使用IP
            $updateStmt = $db->prepare("
                UPDATE tokens SET is_used = TRUE, used_by_ip = ?, used_at = ? 
                WHERE id = ?
            ");
            $updateStmt->execute([$clientIP, $currentTime, $tokenData['id']]);
            
            // 设置session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['token'] = $token;
            $_SESSION['ip'] = $clientIP;
            $_SESSION['expire_time'] = time() + (Config::SESSION_EXPIRE_MINUTES * 60);
            
            return true;
        }
        
        return false;
    }
    
    public static function createToken($createdBy) {
        $db = Config::getDB();
        
        $token = bin2hex(random_bytes(32));
        
        // 使用PHP计算过期时间，确保准确性
        $currentTime = self::getCurrentCSTTime();
        $expiresAt = date('Y-m-d H:i:s', strtotime($currentTime) + (Config::TOKEN_EXPIRE_MINUTES * 60));
        
        $stmt = $db->prepare("
            INSERT INTO tokens (token, created_by, expires_at) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$token, $createdBy, $expiresAt]);
        
        return [
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_by' => $createdBy,
            'current_time' => $currentTime
        ];
    }
    
    public static function getAllTokens() {
        $db = Config::getDB();
        $currentTime = self::getCurrentCSTTime();
        
        $stmt = $db->prepare("
            SELECT * FROM tokens ORDER BY created_at DESC
        ");
        $stmt->execute();
        
        $tokens = $stmt->fetchAll();
        
        // 在PHP中判断状态，避免数据库时区问题
        foreach ($tokens as &$token) {
            if ($token['is_used']) {
                $token['status'] = '已使用';
            } elseif (strtotime($token['expires_at']) < strtotime($currentTime)) {
                $token['status'] = '已过期';
            } else {
                $token['status'] = '未使用';
            }
            
            // 格式化时间显示
            $token['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($token['created_at']));
            $token['expires_at_formatted'] = date('Y-m-d H:i:s', strtotime($token['expires_at']));
            if ($token['used_at']) {
                $token['used_at_formatted'] = date('Y-m-d H:i:s', strtotime($token['used_at']));
            }
        }
        
        return $tokens;
    }
    
    public static function verifySession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['token'], $_SESSION['ip'], $_SESSION['expire_time'])) {
            return false;
        }
        
        if ($_SESSION['expire_time'] < time()) {
            session_destroy();
            return false;
        }
        
        if ($_SESSION['ip'] !== self::getClientIP()) {
            session_destroy();
            return false;
        }
        
        // 更新session有效期
        $_SESSION['expire_time'] = time() + (Config::SESSION_EXPIRE_MINUTES * 60);
        
        return true;
    }
    
    public static function deleteToken($tokenId) {
        $db = Config::getDB();
        
        $stmt = $db->prepare("DELETE FROM tokens WHERE id = ?");
        return $stmt->execute([$tokenId]);
    }
    
    private static function cleanExpiredTokens() {
        $db = Config::getDB();
        $currentTime = self::getCurrentCSTTime();
        
        // 获取所有过期的token
        $stmt = $db->prepare("SELECT id, expires_at FROM tokens WHERE is_used = FALSE");
        $stmt->execute();
        $tokens = $stmt->fetchAll();
        
        foreach ($tokens as $token) {
            if (strtotime($token['expires_at']) < strtotime($currentTime)) {
                $deleteStmt = $db->prepare("DELETE FROM tokens WHERE id = ?");
                $deleteStmt->execute([$token['id']]);
            }
        }
    }
    
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
    
    // 获取当前UTC+8时间字符串
    public static function getCurrentCSTTime() {
        return date('Y-m-d H:i:s');
    }
    
    // 调试函数：显示时间信息
    public static function debugTimeInfo() {
        $db = Config::getDB();
        
        // 获取数据库时间
        $stmt = $db->query("SELECT NOW() as db_time, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
        $dbInfo = $stmt->fetch();
        
        return [
            'php_time' => self::getCurrentCSTTime(),
            'db_time' => $dbInfo['db_time'],
            'global_timezone' => $dbInfo['global_tz'],
            'session_timezone' => $dbInfo['session_tz'],
            'php_timezone' => date_default_timezone_get()
        ];
    }
}
?>