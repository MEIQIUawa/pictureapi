<?php
session_start();

// 包含当前用户管理文件
require_once 'current_user.php';

// 维护模式开关 - 设置为true时启用维护模式
$maintenance_mode = false;

// 如果启用维护模式，显示维护信息
if ($maintenance_mode) {
    die('<div style="text-align: center; padding: 50px; font-family: Inter, sans-serif;"><h1>系统正在维护</h1><p>请稍后再试</p></div>');
}

// 处理退出登录
if (isset($_GET['logout'])) {
    // 清除当前用户信息
    clearCurrentUser();
    session_destroy();
    header('Location: login.php');
    exit;
}

// 检查会话是否超时
if (checkSessionTimeout()) {
    // 会话超时，清除会话并重定向到登录页
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// 检查是否已登录
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

// 检查当前会话用户是否与存储的用户一致
$current_user = getCurrentUser();
if (empty($current_user) || $current_user !== ($_SESSION['admin_username'] ?? '')) {
    // 用户不匹配，清除会话并重定向到登录页
    session_destroy();
    header('Location: login.php');
    exit;
}

// 更新用户活动时间
updateCurrentUserTime();

// 获取目录中的图片
function getImages($directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    return glob($directory . '/*.{jpg,jpeg,png,gif,bmp}', GLOB_BRACE);
}

// 处理重名文件的移动函数
function moveFile($filePath, $targetDir) {
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $fileName = basename($filePath);
    $targetPath = $targetDir . '/' . $fileName;
    
    // 如果目标文件已存在，则生成新文件名
    if (file_exists($targetPath)) {
        $pathInfo = pathinfo($fileName);
        $baseName = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
        
        $counter = 1;
        do {
            $newFileName = $baseName . '_' . $counter . $extension;
            $targetPath = $targetDir . '/' . $newFileName;
            $counter++;
        } while (file_exists($targetPath));
    }
    
    // 移动文件
    if (rename($filePath, $targetPath)) {
        return $targetPath;
    }
    
    return false;
}

$section = isset($_GET['section']) ? $_GET['section'] : null;
$images = [];
$currentIndex = 0;

if ($section === 'pc') {
    $images = getImages('pc');
} elseif ($section === 'phone') {
    $images = getImages('phone');
}

if (isset($_GET['action']) && isset($_GET['index'])) {
    $currentIndex = (int)$_GET['index'];
    if ($_GET['action'] === 'next') {
        $currentIndex = min($currentIndex + 1, count($images) - 1);
    } elseif ($_GET['action'] === 'prev') {
        $currentIndex = max($currentIndex - 1, 0);
    } elseif ($_GET['action'] === 'reject') {
        if ($section === 'pc') {
            moveFile($images[$currentIndex], 'notpc');
        } elseif ($section === 'phone') {
            moveFile($images[$currentIndex], 'notphone');
        }
        header('Location: ctrl.php?section=' . $section . '&index=' . $currentIndex);
        exit;
    } elseif ($_GET['action'] === 'approve') {
        if ($section === 'pc') {
            moveFile($images[$currentIndex], 'apipc');
        } elseif ($section === 'phone') {
            moveFile($images[$currentIndex], 'apiphone');
        }
        header('Location: ctrl.php?section=' . $section . '&index=' . $currentIndex);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>图片控制面板</title>
    <script src="https://cdn.jsdmirror.com/npm/vue@2.6.14/dist/vue.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
            color: #2d3748;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .tabs {
            display: flex;
            gap: 15px;
        }
        .tab {
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        .tab.active, .tab:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .logout-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #718096;
            font-size: 14px;
        }
        .review-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #4a5568;
        }
        .low-quality-badge {
            position: absolute;
            top: 5px;    /* 更靠近上边缘 */
            right: 5px;  /* 更靠近右边缘 */
            background-color: #ff4444;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
            transform: rotate(-15deg); /* 新增：斜着显示 */
            transform-origin: center center; /* 新增：旋转中心点 */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); /* 新增：添加阴影增强可见性 */
        }
        
        .image-container {
            position: relative;
            margin-bottom: 30px;
        }
        .current-image {
            max-width: 100%;
            max-height: 70vh;
            border-radius: 8px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .current-image:hover {
            transform: scale(1.01);
        }
        .image-info {
            margin-top: 15px;
            font-size: 14px;
            color: #718096;
            word-wrap: break-word;
            word-break: break-all;
            white-space: normal;
        }
        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: #48bb78;
            color: white;
        }
        .btn-primary:hover {
            background: #38a169;
        }
        .btn-secondary {
            background: #e53e3e;
            color: white;
        }
        .btn-secondary:hover {
            background: #c53030;
        }
        .btn-nav {
            background: #667eea;
            color: white;
        }
        .btn-nav:hover {
            background: #5a67d8;
        }
        .keyboard-hints {
            margin-top: 30px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
        }
        .hint-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #4a5568;
        }
        .hints {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .hint-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .key {
            background: #edf2f7;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 500;
            min-width: 40px;
            text-align: center;
        }
        .no-images {
            padding: 40px;
            text-align: center;
            color: #a0aec0;
        }
        .no-images i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            text-align: center;
        }
        .message.success {
            background: #c6f6d5;
            color: #22543d;
        }
        .message.error {
            background: #fed7d7;
            color: #742a2a;
        }
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            .tabs {
                flex-wrap: wrap;
                justify-content: center;
            }
            .controls {
                flex-wrap: wrap;
            }
            .btn {
                flex: 1;
                justify-content: center;
            }
        }

        .qq-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="header">
            <div class="header-content">
                <div class="logo">图片审核系统</div>
                <div class="header-right">
                    <div class="tabs">
                        <a :href="'ctrl.php?section=pc'" class="tab" :class="{active: section === 'pc'}">PC图片</a>
                        <a :href="'ctrl.php?section=phone'" class="tab" :class="{active: section === 'phone'}">手机图片</a>
                    </div>
                    <div class="user-info">
                        <span>欢迎，管理员</span>
                    </div>
                    <a href="ctrl.php?logout=1" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        退出
                    </a>
                </div>
            </div>
        </div>

        <div class="container">
            <?php
            // 显示操作结果消息
            if (isset($_GET['moved']) && $_GET['moved'] === '1') {
                echo '<div class="message success">
                    <i class="fas fa-check-circle"></i> 图片已成功移动
                </div>';
            } elseif (isset($_GET['moved']) && $_GET['moved'] === '0') {
                echo '<div class="message error">
                    <i class="fas fa-exclamation-circle"></i> 图片移动失败
                </div>';
            } elseif (isset($_GET['renamed']) && $_GET['renamed'] === '1') {
                echo '<div class="message success">
                    <i class="fas fa-info-circle"></i> 检测到重名文件，已自动重命名
                </div>';
            }
            ?>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number">{{ pcImagesCount }}</div>
                    <div class="stat-label">待审核PC图片</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ phoneImagesCount }}</div>
                    <div class="stat-label">待审核手机图片</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ totalReviewed }}</div>
                    <div class="stat-label">已审核图片</div>
                </div>
            </div>

            <div class="review-section" v-if="section && images.length">
                <div class="section-title">
                    {{ section === 'pc' ? 'PC图片审核' : '手机图片审核' }}
                </div>

                <div class="image-container">
                    <img :src="images[currentIndex]" class="current-image" alt="审核图片" @load="checkImageQuality">
                    <div class="low-quality-badge" v-if="lowQuality">低画质</div>
                    <div class="image-info">
                        图片 {{ currentIndex + 1 }} / {{ images.length }} - <?php echo basename($images[$currentIndex] ?? ''); ?>
                    </div>
                </div>

                <div class="controls">
                    <button class="btn btn-nav" @click="navigate('prev')">
                        <i class="fas fa-arrow-left"></i> 上一张
                    </button>
                    <button class="btn btn-secondary" @click="rejectImage">
                        <i class="fas fa-times"></i> 不通过
                    </button>
                    <button class="btn btn-primary" @click="approveImage">
                        <i class="fas fa-check"></i> 通过
                    </button>
                    <button class="btn btn-nav" @click="navigate('next')">
                        下一张 <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <div class="keyboard-hints">
                    <div class="hint-title">键盘快捷键</div>
                    <div class="hints">
                        <div class="hint-item">
                            <span class="key">←</span>
                            <span>上一张</span>
                        </div>
                        <div class="hint-item">
                            <span class="key">→</span>
                            <span>下一张</span>
                        </div>
                        <div class="hint-item">
                            <span class="key">↑</span>
                            <span>不通过</span>
                        </div>
                        <div class="hint-item">
                            <span class="key">↓</span>
                            <span>通过</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="review-section" v-else-if="section && !images.length">
                <div class="no-images">
                    <i class="fas fa-folder-open"></i>
                    <h3>没有待审核的图片</h3>
                    <p>当前{{ section === 'pc' ? 'PC' : '手机' }}文件夹中没有需要审核的图片</p>
                </div>
            </div>

            <div class="review-section" v-else>
                <div class="no-images">
                    <i class="fas fa-hand-point-up"></i>
                    <h3>请选择审核类别</h3>
                    <p>点击上方标签选择要审核的图片类别</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                section: '<?php echo $section; ?>',
                images: <?php echo json_encode($images); ?>,
                currentIndex: <?php echo $currentIndex; ?>,
                pcImagesCount: <?php echo count(getImages('pc')); ?>,
                phoneImagesCount: <?php echo count(getImages('phone')); ?>, 
                totalReviewed: <?php echo count(getImages('apipc')) + count(getImages('apiphone')) + count(getImages('notpc')) + count(getImages('notphone')); ?>,
                lowQuality: false, // 新增：低画质标志
                fullscreenMode: localStorage.getItem('fullscreenMode') === 'true' // 从localStorage读取全屏状态
            },
            methods: {
                navigate(action) {
                    if (action === 'prev' && this.currentIndex > 0) {
                        window.location.href = `ctrl.php?section=${this.section}&action=prev&index=${this.currentIndex}`;
                    } else if (action === 'next' && this.currentIndex < this.images.length - 1) {
                        window.location.href = `ctrl.php?section=${this.section}&action=next&index=${this.currentIndex}`;
                    }
                },
                approveImage() {
                    // 检查是否为低画质图片
                    if (this.lowQuality) {
                        // 显示提示信息
                        alert('该图片为低画质，不予通过！');
                        return; // 不执行后续操作
                    }
                    window.location.href = `ctrl.php?section=${this.section}&action=approve&index=${this.currentIndex}`;
                },
                rejectImage() {
                    window.location.href = `ctrl.php?section=${this.section}&action=reject&index=${this.currentIndex}`;
                },
                handleKeydown(e) {
                    if (e.key === 'ArrowLeft') {
                        this.navigate('prev');
                    } else if (e.key === 'ArrowRight') {
                        this.navigate('next');
                    } else if (e.key === 'ArrowUp') {
                        this.rejectImage();
                    } else if (e.key === 'ArrowDown') {
                        // 检查是否为低画质图片
                        if (this.lowQuality) {
                            alert('该图片为低画质，不予通过！');
                            return; // 不执行后续操作
                        }
                        this.approveImage();
                    } else if (e.key === 'F8') {
                        // 新增：F8键切换全屏模式
                        this.toggleFullscreen();
                    } else if (e.key === 'Escape' && this.fullscreenMode) {
                        // 新增：ESC键退出全屏模式
                        this.toggleFullscreen();
                    }
                },
                
                // 新增：切换全屏模式方法
                toggleFullscreen() {
                    this.fullscreenMode = !this.fullscreenMode;
                    // 保存全屏状态到localStorage
                    localStorage.setItem('fullscreenMode', this.fullscreenMode.toString());
                    
                    if (this.fullscreenMode) {
                        // 进入全屏模式：隐藏所有非必要元素
                        document.querySelector('.header').style.display = 'none';
                        document.querySelector('.stats').style.display = 'none';
                        document.querySelector('.controls').style.display = 'none';
                        document.querySelector('.keyboard-hints').style.display = 'none';
                        
                        // 放大图片显示
                        document.querySelector('.image-container').style.margin = '0';
                        document.querySelector('.current-image').style.maxHeight = '90vh';
                        document.querySelector('.current-image').style.maxWidth = '90vw';
                        
                        // 滚动图片到屏幕中心（使用instant避免性能问题）
                        this.scrollImageToCenter();

                        alert('已进入全屏审核模式，仅可使用快捷键操作，按ESC键退出');
                    } else {
                        // 退出全屏模式：恢复所有元素显示
                        document.querySelector('.header').style.display = 'flex';
                        document.querySelector('.stats').style.display = 'flex';
                        document.querySelector('.controls').style.display = 'flex';
                        document.querySelector('.keyboard-hints').style.display = 'block';
                        
                        // 恢复图片样式
                        document.querySelector('.image-container').style.margin = '';
                        document.querySelector('.current-image').style.maxHeight = '';
                        document.querySelector('.current-image').style.maxWidth = '';
                        
                        // 先清除localStorage中的全屏状态，再设置this.fullscreenMode
                        localStorage.removeItem('fullscreenMode');
                        this.fullscreenMode = false; // 明确设置为false
                    }
                },
                
                // 新增：滚动图片到屏幕中心
                scrollImageToCenter() {
                    const imageContainer = document.querySelector('.image-container');
                    if (imageContainer) {
                        const rect = imageContainer.getBoundingClientRect();
                        const windowHeight = window.innerHeight;
                        const windowWidth = window.innerWidth;
                        
                        // 计算需要滚动的距离
                        const scrollTop = rect.top + (rect.height / 2) - (windowHeight / 2);
                        const scrollLeft = rect.left + (rect.width / 2) - (windowWidth / 2);
                        
                        // 使用instant滚动避免性能问题
                        window.scrollTo({
                            top: window.scrollY + scrollTop,
                            left: window.scrollX + scrollLeft,
                            behavior: 'instant'
                        });
                    }
                },
                
                // 新增：检查图片清晰度
                checkImageQuality() {
                    this.lowQuality = false;
                    const imgElement = document.querySelector('.current-image');
                    if (imgElement && imgElement.complete && imgElement.naturalWidth) {
                        const width = imgElement.naturalWidth;
                        const height = imgElement.naturalHeight;
                        
                        // 判断是否低于1920x1080或1080x1920像素
                        if ((width < 1920 && height < 1080) || (width < 1080 && height < 1920)) {
                            this.lowQuality = true;
                        }
                    }
                }
            },
            mounted() {
                window.addEventListener('keydown', this.handleKeydown);
                
                // 获取QQ昵称
                this.getQQNickname();
                
                // 新增：监听图片加载事件
                const imgElement = document.querySelector('.current-image');
                if (imgElement) {
                    if (imgElement.complete) {
                        this.checkImageQuality();
                    } else {
                        imgElement.addEventListener('load', () => {
                            this.checkImageQuality();
                        });
                    }
                }
                
                // 新增：页面加载时检查是否需要进入全屏模式
                if (this.fullscreenMode) {
                    // 使用setTimeout确保DOM完全加载后再执行全屏操作
                    setTimeout(() => {
                        this.toggleFullscreen();
                    }, 100);
                }
                
                // 新增：页面加载时自动滚动图片到中心
                if (this.images.length > 0) {
                    setTimeout(() => {
                        this.scrollImageToCenter();
                    }, 200);
                }
            },
            beforeDestroy() {
                window.removeEventListener('keydown', this.handleKeydown);
            }
        });
    </script>
</body>
</html>
