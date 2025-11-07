<?php
class Config {
    private static $db = null;
    
    // Token过期时间（分钟）
    const TOKEN_EXPIRE_MINUTES = 60;
    
    // Session过期时间（分钟）
    const SESSION_EXPIRE_MINUTES = 60;
    
    // 最大文件大小（字节）
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    
    // 允许的文件类型
    const ALLOWED_TYPES = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/bmp'
    ];
    
    public static function getDB() {
        if (self::$db === null) {
            try {
                self::$db = new PDO("mysql:host=156.233.233.146;port=3306;dbname=test123;charset=utf8mb4", "test123", "test123", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                throw new Exception("数据库连接失败: " . $e->getMessage());
            }
        }
        return self::$db;
    }
}
?>