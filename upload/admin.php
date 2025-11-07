<?php
require_once 'AdminAuth.php';
require_once 'TokenAuth.php';

if (!AdminAuth::isLoggedIn()) {
    header('Location: admin_login.php');
    exit;
}

$message = '';
$tokens = TokenAuth::getAllTokens();

// 生成新token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_token'])) {
    $createdBy = AdminAuth::getCurrentAdmin();
    $tokenData = TokenAuth::createToken($createdBy);
    $message = "Token生成成功: " . $tokenData['token'];
}

// 删除token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_token'])) {
    $tokenId = $_POST['token_id'];
    if (TokenAuth::deleteToken($tokenId)) {
        $message = "Token删除成功";
    } else {
        $message = "Token删除失败";
    }
    $tokens = TokenAuth::getAllTokens(); // 刷新列表
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token管理系统 - 管理员面板</title>
    <link href="https://cdn.jsdmirror.com/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .admin-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .admin-content {
            padding: 30px;
        }
        .token-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fafafa;
        }
        .status-used { color: #28a745; font-weight: bold; }
        .status-expired { color: #dc3545; font-weight: bold; }
        .status-available { color: #17a2b8; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Token管理系统</h1>
            <p>管理员面板 - 欢迎, <?php echo htmlspecialchars(AdminAuth::getCurrentAdmin()); ?></p>
            <a href="admin_logout.php" class="btn btn-light">退出登录</a>
        </div>

        <div class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- 生成Token表单 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">生成新Token</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <button type="submit" name="generate_token" class="btn btn-primary">
                            生成新Token (有效期30分钟)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Token列表 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Token列表</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tokens)): ?>
                        <p class="text-muted">暂无Token</p>
                    <?php else: ?>
                        <?php foreach ($tokens as $token): ?>
                            <div class="token-card">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong>Token:</strong> <?php echo htmlspecialchars($token['token']); ?><br>
                                        <strong>创建者:</strong> <?php echo htmlspecialchars($token['created_by']); ?><br>
                                        <strong>创建时间:</strong> <?php echo $token['created_at']; ?><br>
                                        <strong>过期时间:</strong> <?php echo $token['expires_at']; ?><br>
                                        <strong>状态:</strong> 
                                        <span class="status-<?php echo $token['status'] === '已使用' ? 'used' : ($token['status'] === '已过期' ? 'expired' : 'available'); ?>">
                                            <?php echo $token['status']; ?>
                                        </span><br>
                                        <?php if ($token['used_by_ip']): ?>
                                            <strong>使用IP:</strong> <?php echo htmlspecialchars($token['used_by_ip']); ?><br>
                                            <strong>使用时间:</strong> <?php echo $token['used_at']; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?php if ($token['status'] === '未使用'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                                <button type="submit" name="delete_token" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('确定要删除这个Token吗？')">
                                                    删除
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdmirror.com/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>