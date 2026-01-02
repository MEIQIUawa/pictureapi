# Wallpaper API Site

A dynamic wallpaper API service that automatically serves appropriate wallpapers based on device type detection.

**Read this in other languages: [English](README.md), [中文](README_CN.md).**

## 🌟 Features

- **Device Detection**: Automatically detects and serves wallpapers for PC or mobile devices
- **Dynamic Backgrounds**: Real-time wallpaper serving with automatic device adaptation
- **Upload System**: Support for uploading wallpapers with progress tracking
- **Statistics Dashboard**: Real-time visitor statistics and usage tracking
- **Responsive Design**: Beautiful, responsive web interface
- **API Documentation**: Comprehensive API documentation built-in
- **Installation Wizard**: Easy-to-use web-based installation process

## 🚀 Quick Start

### Prerequisites

- PHP version 7.4 or higher, version 8.0 recommended
- MySQL database version 5.7 or higher
- Web server (Apache/Nginx)

### Installation

1. Clone the repository:
```bash
git clone https://github.com/MEIQIUawa/pictureapi.git
mkdir pictureapi/public && mv pictureapi/* pictureapi/public
echo '<div>Hello World!</div>' > pictureapi/desc.txt
cd pictureapi
```

2. Upload files to your web server's public directory

3. Access the site via browser and follow the installation wizard

4. Complete the installation by configuring database settings

### Manual Installation

1. Create a MySQL database
2. Import the database schema (if provided)
3. Configure database settings in the installation wizard
4. Complete the setup process
5. Default backend login addresses: /api/login.php (image review) and /upload/admin.php (upload management)

## 📖 API Usage

### Basic Usage

```javascript
// Get wallpaper for PC
fetch('/api?equ=pc')
  .then(response => response.blob())
  .then(blob => {
    document.body.style.background = `url(${URL.createObjectURL(blob)}) center/cover`;
  });

// Get wallpaper for mobile
fetch('/api?equ=phone')
  .then(response => response.blob())
  .then(blob => {
    document.body.style.background = `url(${URL.createObjectURL(blob)}) center/cover`;
  });
```

### CSS Background

```css
body {
  background-image: url("/api?equ=pc");
  background-size: cover;
  background-position: center;
}
```

## 🔧 Configuration

The system automatically handles configuration through the web-based installer. Key configuration options include:

- Database connection settings
- File upload limits
- Allowed file types
- API access controls

## 📊 Statistics

The system provides real-time statistics including:
- Total visits count
- PC vs Mobile usage
- Upload statistics
- API usage metrics

## 🔒 Security Features

- File type validation
- Upload size limits
- Database security
- Input sanitization
- Error handling

## 🤝 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## 📄 License

This project is licensed under the GNU General Public License v3.0 (GPL-3.0). See the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with PHP and modern web technologies
- Responsive design for all devices
- Easy-to-use installation process

---

**Note**: This project is developed for educational and practical purposes. Please ensure compliance with applicable laws and regulations when deploying.
