<?php
$counterFile = './api/num.txt';

// 获取访问次数
function getVisits($file) {
    if (!file_exists($file)) {
        file_put_contents($file, '0'); // 如果文件不存在，初始化为0
    }
    return intval(file_get_contents($file)); // 读取文件内容并转换为整数
}

// 每次访问增加计数
$handle = fopen($counterFile, 'c+'); // 以读写模式打开文件，如果文件不存在则创建
if ($handle === false) {
    die("Failed to open file: $counterFile");
}

if (flock($handle, LOCK_EX)) { // 获取排他锁
    $visits = getVisits($counterFile); // 获取当前访问次数
    $newVisits = $visits + 1; // 增加访问次数
    ftruncate($handle, 0); // 清空文件内容
    rewind($handle); // 将文件指针移到文件开头
    fwrite($handle, $newVisits); // 写入新的访问次数
    fflush($handle); // 刷新输出缓冲区，确保数据写入文件
    flock($handle, LOCK_UN); // 释放锁
} else {
    die("Failed to acquire lock on file: $counterFile");
}

fclose($handle); // 关闭文件句柄

?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>壁纸API</title>
    <script type="text/javascript" src="https://myhkw.cn/player/js/jquery.min.js"></script>
    <style>
        @font-face {
        	font-family: 'Aaohmygod';
        	src: url('Aaohmygod被你萌化啦.ttf') format('truetype');
        }
        
        body {
        	margin: 0;
        	font-family: 'Aaohmygod', sans-serif;
        	background-color: #f5f7fa;
        	display: flex;
        	justify-content: center;
        	align-items: center;
        	height: 100vh;
        	overflow: hidden;
        	background-image: url("/api/?equ=pc");
        	background-position: center center;
        	background-repeat: no-repeat;
        	background-attachment: fixed;
        	background-size: cover;
        }
        
        @media only screen and (max-width: 968px) {
        	body {
        		background-image: url("/api/?equ=phone");
        	}
        
        	.navbar {
        		height: 50px;
        	}
        
        	.navbar a {
        		font-size: 2vh;
        	}
        }
        
        .navbar {
        	background-color: rgba(51, 51, 51, 0.6);
        	overflow: hidden;
        	display: flex;
        	justify-content: space-around;
        	align-items: center;
        	padding: 10px 0;
        	position: fixed;
        	width: 100%;
        	top: 0;
        	z-index: 1000;
        	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
        	font-size: 3vh;
        }
        
        .navbar a {
        	color: white;
        	text-decoration: none;
        	padding: 14px 20px;
        	text-align: center;
        	transition: color 0.3s ease;
        }
        
        .navbar a:hover {
        	color: #ffcc00;
        }
        
        .content {
        	position: relative;
        	width: 90%;
        	max-width: 1300px;
        	height: 70%;
        	margin-top: 60px;
        	overflow: hidden;
        }
        
        .page {
        	position: absolute;
        	width: 100%;
        	height: 100%;
        	background-color: rgba(255, 255, 255, 0.9);
        	border-radius: 10px;
        	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        	transition: transform 0.6s ease, opacity 0.6s ease;
        	justify-content: center;
        	align-items: center;
        	padding: 20px;
        	box-sizing: border-box;
        	opacity: 0;
        	transform: translateX(100%);
        	word-wrap: break-word;
        	word-break: break-word;
        }
        
        .active {
        	opacity: 1;
        	transform: translateX(0);
        }
        
        .fade-in {
        	animation: fadeIn 1s ease-in-out;
        }
        
        @keyframes fadeIn {
        	from {
        		opacity: 0;
        	}
        
        	to {
        		opacity: 1;
        	}
        }
        
        .writer {
        	position: fixed;
        	right: 10px;
        	bottom: 10px;
        	color: #778899;
        }
        
        .head {
        	position: fixed;
        	left: 10px;
        	top: 2px;
        	color: #778899;
        }
        
        .email {
        	position: relative;
        	display: inline-block;
        	color: #BEBEBE;
        	text-decoration: none;
        	cursor: pointer;
        }
        
        .email::after {
        	content: '';
        	position: absolute;
        	width: 0;
        	height: 2px;
        	background: #778899;
        	left: 0;
        	bottom: -2px;
        	transition: width 0.3s ease;
        }
        
        .email:hover::after {
        	width: 100%;
        }
        
        /* 烟花特效样式 */
        .firework {
        	position: fixed;
        	pointer-events: none;
        	z-index: 9999;
        }
        
        .particle {
        	position: absolute;
        	width: 8px;
        	height: 8px;
        	border-radius: 50%;
        	animation: explode 1s ease-out both;
        	will-change: transform, opacity;
        }
        
        @keyframes explode {
        	0% {
        		transform: translate(-50%, -50%) scale(1);
        		opacity: 1;
        	}
        
        	100% {
        		transform: translate(var(--tx), var(--ty)) scale(3);
        		opacity: 0;
        	}
        }
        
        .thank-you {
        	position: fixed;
        	top: 50%;
        	left: 50%;
        	transform: translate(-50%, -50%);
        	font-size: 2em;
        	color: #ff3366;
        	animation: fadeOut 5s ease;
        	opacity: 0;
        	z-index: 10000;
        	text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
        	background: rgba(255, 255, 255, 0.8);
        	/* 白色半透明背景 */
        	padding: 15px 30px;
        	border-radius: 25px;
        	/* 圆角处理 */
        	box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        	/* 添加阴影增强立体感 */
        	backdrop-filter: blur(5px);
        	/* 可选：背景模糊效果 */
        }
        
        @keyframes fadeOut {
        	0% {
        		opacity: 1;
        	}
        
        	90% {
        		opacity: 1;
        	}
        
        	100% {
        		opacity: 0;
        	}
        }
        
        /* 上传相关样式 */
        .upload-section {
        	margin: 20px 0;
        }
        
        .file-input {
        	display: none;
        }
        
        .upload-btn {
        	background: #4CAF50;
        	color: white;
        	padding: 8px 16px;
        	border: none;
        	border-radius: 4px;
        	cursor: pointer;
        	transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .upload-btn:hover {
        	transform: translateY(-2px);
        	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .progress-bar {
        	width: 100%;
        	height: 20px;
        	background: #ddd;
        	margin-top: 10px;
        	border-radius: 10px;
        	overflow: hidden;
        }
        
        .progress {
        	width: 0%;
        	height: 100%;
        	background: linear-gradient(90deg, #4CAF50, #45a049);
        	transition: width 0.3s ease;
        }
        
        .highlight {
        	background-color: rgba(236, 240, 241, 0.6);
        	/* 半透明背景 */
        	padding: 10px;
        	border-left: 4px solid #2c3e50;
        }
        
        ul {
        	list-style-type: none;
        	padding: 0;
        }
        
        li {
        	margin-bottom: 10px;
        }
        
        /* 可滚动的 div（隐藏默认滚动条） */
        .scrollable-div {
        	overflow: hidden;
        	/* 隐藏滚动条 */
        
        }
        
        /* 内部滚动容器 */
        .scroll-content {
        	width: calc(100% + 20px);
        	/* 抵消隐藏的滚动条宽度 */
        	height: 100%;
        	overflow-y: scroll;
        	/* 允许滚动 */
        	padding-right: 20px;
        	/* 防止文字被遮挡 */
        }
        
        /* 隐藏默认滚动条（兼容 Chrome/Firefox/Safari） */
        .scroll-content::-webkit-scrollbar {
        	display: none;
        	/* Chrome/Safari */
        }
        
        .scroll-content {
        	scrollbar-width: none;
        	/* Firefox */
        }
        
        /* 自定义蓝色进度条 */
        .scroll-progress {
        	position: absolute;
        	top: 0;
        	right: 0;
        	width: 4px;
        	background: #3498db;
        	height: 0;
        	z-index: 10;
        	transition: height 0.05s linear;
        }
        
        /* API文档专用样式 */
        .api-section {
        	margin: 25px 0;
        	padding: 15px;
        	background: rgba(255, 255, 255, 0.8);
        	border-radius: 8px;
        	box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .api-section h3 {
        	color: #2c3e50;
        	margin: 0 0 15px 0;
        	padding-bottom: 8px;
        	border-bottom: 2px solid #3498db;
        }
        
        .param-table {
        	width: 100%;
        	border-collapse: collapse;
        	margin: 15px 0;
        }
        
        .param-table th,
        .param-table td {
        	padding: 12px;
        	border: 1px solid #ddd;
        	text-align: left;
        }
        
        .param-table th {
        	background-color: #f8f9fa;
        }
        
        .code-box {
        	position: relative;
        	margin: 15px 0;
        	background: #f8f9fa;
        	border-radius: 6px;
        	border: 1px solid #e9ecef;
        	overflow: hidden;
        	/* 关键修改 */
        }
        
        .code-box pre {
        	margin: 0;
        	padding: 20px;
        	font-family: 'Courier New', monospace;
        	overflow-x: auto;
        	/* 关键修改 */
        	white-space: pre;
        	/* 关键修改 */
        }
        
        .code-header {
        	position: absolute;
        	right: 10px;
        	top: 10px;
        	z-index: 1;
        	/* 关键修改 */
        }
        
        .copy-btn {
        	padding: 5px 12px;
        	background: #3498db;
        	color: white;
        	border: none;
        	border-radius: 4px;
        	cursor: pointer;
        	transition: all 0.3s;
        }
        
        .copy-btn:hover {
        	background: #2980b9;
        	transform: translateY(-1px);
        	box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* 状态页面样式 */
        .stats-container {
        	display: grid;
        	gap: 20px;
        	padding: 20px;
        	max-width: 800px;
        	margin: 0 auto;
        }
        
        .stat-card {
        	background: rgba(255, 255, 255, 0.9);
        	border-radius: 12px;
        	padding: 20px;
        	display: flex;
        	align-items: center;
        	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        	transition: transform 0.3s;
        }
        
        .stat-card:hover {
        	transform: translateY(-3px);
        }
        
        .stat-icon {
        	font-size: 2em;
        	margin-right: 20px;
        	width: 60px;
        	text-align: center;
        }
        
        .stat-info {
        	flex: 1;
        }
        
        .stat-label {
        	display: block;
        	color: #666;
        	font-size: 1.1em;
        	margin-bottom: 5px;
        }
        
        .stat-value {
        	font-size: 2em;
        	color: #2ecc71;
        	font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="#" onclick="showPage('home')">首页</a>
        <a href="#" onclick="showPage('api')">API文档</a>
        <a href="#" onclick="showPage('submit')">投稿</a>
        <a href="#" onclick="showPage('status')">站点状态</a>
        <a href="https://github.com/MEIQIUawa/pictureapi" target="_blank">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25231.png" alt="GitHub" style="width:20px; vertical-align:middle;"> Github
        </a>
    </div>

    <div class="content">
        <div id="home" class="page active fade-in">
            <div class="head">O首页</div><br>
            <div>
                <div style="font-size: 5vh;"><?php
                    $desc_content = file_get_contents('../desc.txt');
                    if ($desc_content !== false) {
                        echo $desc_content;
                    } else {
                        echo '这是一个壁纸API站点-<span style="font-size: 3vh;">made by MEIQIU</span>';
                    }
                ?></div>
            </div>
            <div class="writer">——MEIQIU</div>
        </div>

        <div id="api" class="page scrollable-div">
            <div class="scroll-progress"></div>
            <div class="scroll-content">
                <div class="head">OAPI文档</div><br>
                <div style="padding: 0 20px;">
                    <!-- API基本说明 -->
                    <section class="api-section">
                        <h3>📖 接口说明</h3>
                        <p>本API提供动态壁纸服务，根据设备类型返回适合的壁纸图片。支持以下特性：</p>
                        <ul>
                            <li>自动设备检测（通过<code>equ</code>参数指定）</li>
                            <li>随机返回高质量图片</li>
                            <li>支持HTTPS安全访问</li>
                            <li>每日自动更新图片库</li>
                        </ul>
                    </section>

                    <!-- 请求示例 -->
                    <section class="api-section">
                        <h3>🌐 请求地址</h3>
                        <div class="code-box">
                            <div class="code-header">
                                <button class="copy-btn" onclick="copyCode(this)">复制</button>
                            </div>
                            <pre><code>GET <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/api?equ=pc   // 电脑壁纸
GET <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/api?equ=phone // 手机壁纸</code></pre>
                        </div>
                    </section>

                    <!-- 参数说明 -->
                    <section class="api-section">
                        <h3>🔧 参数说明</h3>
                        <table class="param-table">
                            <tr>
                                <th>参数</th>
                                <th>必填</th>
                                <th>说明</th>
                                <th>可选值</th>
                            </tr>
                            <tr>
                                <td>equ</td>
                                <td>否</td>
                                <td>设备类型</td>
                                <td>pc / phone</td>
                            </tr>
                        </table>
                    </section>

                    <!-- 使用示例 -->
                    <section class="api-section">
                        <h3>💡 使用示例</h3>
                        <div class="code-box">
                            <div class="code-header">
                                <button class="copy-btn" onclick="copyCode(this)">复制</button>
                            </div>
                            <pre><code>/* CSS背景设置 */
    body {
    background-image: url("<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/api?equ=pc");
    background-size: cover;
    background-position: center;
}</code></pre>
                        </div>

                        <div class="code-box">
                            <div class="code-header">
                                <button class="copy-btn" onclick="copyCode(this)">复制</button>
                            </div>
                            <pre><code>// JavaScript动态获取
fetch('<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/api')
  .then(response => response.blob())
  .then(blob => {
    document.body.style.background = `url(${URL.createObjectURL(blob)}) center/cover`;
  });</code></pre>
                        </div>
                    </section>

                    <!-- 响应说明 -->
                    <section class="api-section">
                        <h3>📤 响应说明</h3>
                        <ul>
                            <li>成功：直接返回图片二进制流（Content-Type: image/jpeg）</li>
                            <li>错误：返回JSON格式响应（示例）：
                                <div class="code-box">
                                    <div class="code-header">
                                        <button class="copy-btn" onclick="copyCode(this)">复制</button>
                                    </div>
                                    <pre><code>{
    "error": "invalid_parameter",
    "message": "无效的设备参数"
}</code></pre>
                                </div>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
            <div class="writer">——MEIQIU</div>
        </div>

        <div id="submit" class="page scrollable-div">
            <div class="scroll-progress"></div>
            <div class="scroll-content">
                <div class="head">O投稿</div><br>
                <div class="upload-section">
                    <button class="upload-btn" onclick="document.getElementById('pc-file').click()">选择电脑壁纸</button>
                    <input type="file" id="pc-file" class="file-input" accept="image/*" onchange="showFileName(this, 'pc-name')">
                    <span id="pc-name" style="margin-left:10px;"></span>
                    <button class="upload-btn" onclick="uploadFile('pc-file', 'pc-progress', 'pc')">上传</button>
                </div>
                <div class="progress-bar">
                    <div id="pc-progress" class="progress"></div>
                </div>

                <div class="upload-section">
                    <button class="upload-btn" onclick="document.getElementById('phone-file').click()">选择手机壁纸</button>
                    <input type="file" id="phone-file" class="file-input" accept="image/*" onchange="showFileName(this, 'phone-name')">
                    <span id="phone-name" style="margin-left:10px;"></span>
                    <button class="upload-btn" onclick="uploadFile('phone-file', 'phone-progress', 'phone')">上传</button>
                </div>
                <div class="progress-bar">
                    <div id="phone-progress" class="progress"></div>
                </div>
                <hr>
                <div style="color: red">
                    <p>我们有强大的审核系统，禁止上传违规图片</p>
                </div>
                <div id="scrollContainer">
                    <p>根据《规范互联网信息服务市场秩序若干规定》，互联网信息服务提供者应当遵循平等、自愿、公平、诚信的原则提供服务，不得实施以下行为：</p>
                    <ul>
                        <li>恶意干扰用户终端上其他互联网信息服务提供者的服务，或者恶意干扰与互联网信息服务相关的软件等产品的下载、安装、运行和升级。</li>
                        <li>捏造、散布虚假事实损害其他互联网信息服务提供者的合法权益，或者诋毁其他互联网信息服务提供者的服务或者产品。</li>
                        <li>恶意对其他互联网信息服务提供者的服务或者产品实施不兼容。</li>
                    </ul>
                    <p>网站主办者应当依法开展互联网信息服务业务，不得发布或传播违法信息，包括但不限于涉及国家安全、社会稳定、淫秽色情、暴力恐怖等内容。</p>
                    <div class="highlight">
                        <h3>实施时间</h3>
                        <p><strong>《规范互联网信息服务市场秩序若干规定》</strong>：自2012年3月15日起施行。</p>
                    </div>
                    <p>这些规定旨在规范互联网信息服务市场秩序，保护互联网信息服务提供者和用户的合法权益，促进互联网行业的健康发展。</p>
                </div>
                <div class="writer">——MEIQIU</div>
            </div>
        </div>

<div id="status" class="page">
    <div class="head">O站点状态</div><br>
    <div class="stats-container">
        <!-- 统计信息卡片 -->
        <div class="stat-card">
            <div class="stat-icon">🖥️</div>
            <div class="stat-info">
                <span class="stat-label">电脑壁纸</span>
                <span id="pc-count" class="stat-value">加载中...</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📱</div>
            <div class="stat-info">
                <span class="stat-label">手机壁纸</span>
                <span id="phone-count" class="stat-value">加载中...</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <span class="stat-label">访问次数</span>
                <span id="visit-count" class="stat-value">加载中...</span>
            </div>
        </div>
    </div>
    <div class="writer">——MEIQIU</div>
</div>
    </div>

    <script>
        // 获取所有滚动容器
        const scrollableDivs = document.querySelectorAll(".scrollable-div");

        // 为每个容器初始化滚动进度条
        scrollableDivs.forEach((container) => {
            const scrollContent = container.querySelector(".scroll-content");
            const scrollProgress = container.querySelector(".scroll-progress");

            // 计算并更新进度条
            function updateScrollProgress() {
                const scrollTop = scrollContent.scrollTop;
                const scrollHeight = scrollContent.scrollHeight - scrollContent.clientHeight;
                const progress = Math.min(100, (scrollTop / scrollHeight) * 100);
                scrollProgress.style.height = `${progress}%`;
            }

            // 初始化
            updateScrollProgress();

            // 监听滚动（优化性能）
            let isUpdating = false;
            scrollContent.addEventListener("scroll", () => {
                if (!isUpdating) {
                    requestAnimationFrame(() => {
                        updateScrollProgress();
                        isUpdating = false;
                    });
                    isUpdating = true;
                }
            });

            // 窗口大小变化时重新计算
            window.addEventListener("resize", updateScrollProgress);
        });

        // 复制代码函数
        function copyCode(button) {
            const codeBox = button.closest('.code-box');
            const code = codeBox.querySelector('code').textContent;

            // 兼容性处理：优先使用现代API，降级到传统方法
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(() => {
                    showCopySuccess(button);
                }).catch(() => {
                    fallbackCopyText(code, button);
                });
            } else {
                fallbackCopyText(code, button);
            }
        }

        // 显示复制成功提示
        function showCopySuccess(button) {
            const originalText = button.textContent;
            button.textContent = '✓ 已复制';
            setTimeout(() => {
                button.textContent = originalText;
            }, 2000);
        }

        // 降级复制方法
        function fallbackCopyText(text, button) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess(button);
                } else {
                    alert('复制失败，请手动选择文本复制');
                }
            } catch (err) {
                alert('复制失败，请手动选择文本复制');
            }
            
            document.body.removeChild(textArea);
        }
    </script>

    <script>
        function showPage(pageId) {
            const pages = document.querySelectorAll('.page');
            pages.forEach(page => {
                page.classList.toggle('active', page.id === pageId);
                page.classList.toggle('fade-in', page.id === pageId);
            });
        }

        function copyToClipboard(element) {
            const text = element.innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('已复制: ' + text);
            });
        }

        function showFileName(input, spanId) {
            const nameSpan = document.getElementById(spanId);
            nameSpan.textContent = input.files[0]?.name || '';
        }

        let fireworkInterval;

        function createFirework(x, y) {
            const particles = [];
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
                particle.style.setProperty('--tx', `${(Math.random() - 0.5) * 100}vw`);
                particle.style.setProperty('--ty', `${(Math.random() - 0.5) * 100}vh`);
                particle.style.left = x + '%';
                particle.style.top = y + '%';
                document.body.appendChild(particle);
                particles.push(particle);
                setTimeout(() => particle.remove(), 1000);
            }
            return particles;
        }

        function showFireworkEffect() {
            const thankYou = document.createElement('div');
            thankYou.className = 'thank-you';
            thankYou.innerHTML = '感谢您的贡献<span style="font-size:0.8em;">\\(^v^)/</span>'; // 缩小颜文字
            document.body.appendChild(thankYou);

            // 随机位置生成
            const getRandomPosition = () => ({
                x: Math.random() * 70 + 15, // 15%-85% 避免边缘显示不全
                y: Math.random() * 70 + 15
            });

            // 首次立即显示
            let pos = getRandomPosition();
            createFirework(pos.x, pos.y);

            // 定时生成新烟花
            fireworkInterval = setInterval(() => {
                pos = getRandomPosition();
                createFirework(pos.x, pos.y);
            }, 500);

            // 5秒后清除
            setTimeout(() => {
                clearInterval(fireworkInterval);
                thankYou.remove();
            }, 5000);
        }

        function uploadFile(inputId, progressId, type) {
            const input = document.getElementById(inputId);
            const file = input.files[0];
            const progressBar = document.getElementById(progressId);

            if (!file) {
                alert('请先选择文件！');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('upload_type', type);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php');

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    progressBar.style.width = `${(e.loaded / e.total) * 100}%`;
                }
            };

            xhr.onload = () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showFireworkEffect();
                        setTimeout(() => {
                            input.value = '';
                            document.getElementById(inputId.replace('file', 'name')).textContent = '';
                            progressBar.style.width = '0%';
                        }, 5000);
                    }
                } else {
                    const error = JSON.parse(xhr.responseText)?.error || '未知错误';
                    alert(`上传失败: ${error}`);
                    progressBar.style.width = '0%';
                }
            };

            xhr.onerror = () => {
                alert('网络错误，请检查连接');
                progressBar.style.width = '0%';
            };

            xhr.send(formData);
        }
    </script>
<script>
// 在showPage函数中添加统计获取
function showPage(pageId) {
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => {
        const isActive = page.id === pageId;
        page.classList.toggle('active', isActive);
        page.classList.toggle('fade-in', isActive);
        
        if(isActive && pageId === 'status') {
            loadStatistics();
        }
    });
}

// 统计信息加载函数
function loadStatistics() {
    fetch('/api/stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('pc-count').textContent = data.pc.toLocaleString();
            document.getElementById('phone-count').textContent = data.phone.toLocaleString();
            document.getElementById('visit-count').textContent = data.visits.toLocaleString();
        })
        .catch(error => {
            console.error('统计信息加载失败:', error);
            document.querySelectorAll('.stat-value').forEach(el => {
                el.textContent = '数据不可用';
                el.style.color = '#e74c3c';
            });
        });
}
</script>
</body>

</html>
