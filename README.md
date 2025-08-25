# QR Code Generator

A simple PHP-based QR Code Generator web application for generating scannable QR codes from URLs or text.

## 🚀 Features
- Generate QR codes instantly from user input (URL or text)
- Download generated QR codes as image files (PNG/SVG)
- Lightweight and easy to deploy
- Built with PHP and the [Bacon QR Code](https://github.com/Bacon/BaconQrCode) library

## 📂 Project Structure
```
QR/
│── Qr_generator.php       # Main QR generator script
│── composer.json          # Composer dependencies
│── composer.lock          # Dependency lock file
│── vendor/                # Third-party libraries (Bacon QR Code, etc.)
│── .htaccess              # Apache rewrite rules (optional)
│── reshot-icon-label-qr-code-CWA5ZSLBFT.svg # QR icon
│── robots.txt, sitemap.xml # SEO related files
```

## 🛠️ Requirements
- PHP 7.4 or higher
- Composer (for dependency management)
- Web server (Apache/Nginx)

## ⚙️ Installation
1. Clone or download this repository.
2. Navigate to the project folder:
   ```bash
   cd QR
   ```
3. Install dependencies with Composer:
   ```bash
   composer install
   ```
4. Deploy the project on a PHP-supported web server.

## ▶️ Usage
1. Start your local PHP server:
   ```bash
   php -S localhost:8000
   ```
2. Open the app in your browser:
   ```
   http://localhost:8000/Qr_generator.php
   ```
3. Enter a URL or text and click **Generate** to get your QR code.

## 📦 Dependencies
- [bacon/bacon-qr-code](https://github.com/Bacon/BaconQrCode) – Core QR code generation library

## 🌐 Deployment
- Upload the project folder to your hosting provider (e.g., Apache, Nginx with PHP support).
- Ensure `.htaccess` is enabled if using Apache.

## 📜 License
This project is licensed under the MIT License.
