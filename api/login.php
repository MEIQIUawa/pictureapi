<?php
session_start();

// 包含当前用户管理文件
require_once 'current_user.php';

// 包含upload目录中的数据库配置和AdminAuth类
require_once '../upload/config.php';
require_once '../upload/AdminAuth.php';

// 维护模式开关 - 设置为true时启用维护模式
$maintenance_mode = false;

// 如果启用维护模式，显示维护信息并阻止登录
if ($maintenance_mode) {
    // 如果用户已登录，显示维护信息
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        die('<div style="text-align: center; padding: 50px; font-family: Inter, sans-serif;"><h1>系统正在维护</h1><p>请稍后再试</p></div>');
    }
    // 如果用户尝试登录，显示维护信息并阻止登录
    if (isset($_POST['username']) || isset($_POST['password'])) {
        die('<div style="text-align: center; padding: 50px; font-family: Inter, sans-serif;"><h1>系统正在维护</h1><p>暂时不允许登录，请稍后再试</p></div>');
    }
    // 显示登录页面的维护信息
    die('<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>系统维护</title><style>body{font-family:Inter,sans-serif;text-align:center;padding:50px;}h1{color:#667eea;}</style></head><body><h1>系统正在维护</h1><p>请稍后再试</p></body></html>');
}

// 处理退出登录
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// 检查是否已登录（使用AdminAuth类）
if (AdminAuth::isLoggedIn()) {
    header('Location: ctrl.php');
    exit;
}

// 处理admin登录验证
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $login_result = AdminAuth::login($username, $password);
    
    if ($login_result['success']) {
        // 登录成功，设置session变量
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
        
        // 设置当前用户
        setCurrentUser($username);
        
        header('Location: ctrl.php');
        exit;
    } else {
        $error_message = $login_result['message'];
    }
}

// 删除这里的超时提示代码
// if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
//     echo '<div class="error-message">会话已超时，请重新登录</div>';
// }
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 图片审核系统</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 400px;
            padding: 40px;
            text-align: center;
        }
        .logo {
            margin-bottom: 30px;
        }
        .logo h1 {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 5px;
        }
        .logo p {
            color: #718096;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4a5568;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-btn:hover {
            background: #5a67d8;
        }
        .error-message {
            color: #e53e3e;
            margin-top: 15px;
            padding: 10px;
            background: #fed7d7;
            border-radius: 6px;
            font-size: 14px;
        }
        .security-info {
            margin-top: 20px;
            padding: 10px;
            background: #f7fafc;
            border-radius: 6px;
            font-size: 12px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>图片审核系统</h1>
            <p>管理员登录</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">登录</button>
        </form>
        
        <div class="security-info">
            <p>安全提示：同IP密码错误超过3次将封禁10分钟</p>
        </div>
        
        <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
            <div class="error-message">会话已超时，请重新登录</div>
        <?php endif; ?>
    </div>
</body>
</html>
