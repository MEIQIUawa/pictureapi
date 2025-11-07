# 壁纸API站点

一个动态壁纸API服务，根据设备类型自动提供合适的壁纸图片。

**这里可以切换语言: [English](README.md), [中文](README_CN.md).**

## 🌟 功能特性

- **设备检测**: 自动检测并为PC或移动设备提供壁纸
- **动态背景**: 实时壁纸服务，支持设备自适应
- **上传系统**: 支持壁纸上传，带进度跟踪
- **统计面板**: 实时访问统计和使用情况跟踪
- **响应式设计**: 美观的响应式Web界面
- **API文档**: 内置完整的API文档
- **安装向导**: 易于使用的基于Web的安装过程

## 🚀 快速开始

### 环境要求

- PHP 7.4 或更高版本
- MySQL/MariaDB 数据库
- Web服务器 (Apache/Nginx)

### 安装步骤

1. 克隆仓库：
```bash
git clone <你的仓库地址>
cd picapi-pub
```

2. 将文件上传到Web服务器的公共目录

3. 通过浏览器访问站点并按照安装向导操作

4. 配置数据库设置完成安装

### 手动安装

1. 创建MySQL数据库
2. 导入数据库架构（如果提供）
3. 在安装向导中配置数据库设置
4. 完成设置过程

## 📖 API使用

### 基本用法

```javascript
// 获取PC壁纸
fetch('/api?equ=pc')
  .then(response => response.blob())
  .then(blob => {
    document.body.style.background = `url(${URL.createObjectURL(blob)}) center/cover`;
  });

// 获取手机壁纸
fetch('/api?equ=phone')
  .then(response => response.blob())
  .then(blob => {
    document.body.style.background = `url(${URL.createObjectURL(blob)}) center/cover`;
  });
```

### CSS背景设置

```css
body {
  background-image: url("/api?equ=pc");
  background-size: cover;
  background-position: center;
}
```

## 🏗️ 项目结构
picapi-pub/
├── public/
│   ├── api/           # API端点
│   ├── upload/        # 上传处理
│   ├── css/          # 样式表
│   ├── js/           # JavaScript库
│   ├── index.php     # 主界面
│   └── install.php   # 安装向导
└── desc.txt          # 站点描述

## 🔧 配置

系统通过基于Web的安装程序自动处理配置。主要配置选项包括：

- 数据库连接设置
- 文件上传限制
- 允许的文件类型
- API访问控制

## 📊 统计功能

系统提供实时统计信息，包括：
- 总访问次数
- PC与移动设备使用情况
- 上传统计
- API使用指标

## 🔒 安全特性

- 文件类型验证
- 上传大小限制
- 数据库安全
- 输入清理
- 错误处理

## 🤝 贡献指南

欢迎贡献！请随时提交拉取请求或为错误和功能请求打开问题。

## 📄 许可证

本项目采用GNU通用公共许可证v3.0 (GPL-3.0)。详见[LICENSE](LICENSE)文件。

## 🙏 致谢

- 使用PHP和现代Web技术构建
- 适用于所有设备的响应式设计
- 易于使用的安装过程

---

**注意**: 本项目为教育和实用目的开发。部署时请确保符合适用的法律法规。

## 📞 技术支持

如有问题，请通过GitHub Issues提交问题报告。
