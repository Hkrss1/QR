<?php
// Security-related headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

$qrImageData = null;
$result = null;

function derive_filename_base(string $raw): string {
    $raw = trim($raw);
    $candidate = $raw;

    $parts = @parse_url($raw);
    if ($parts !== false && isset($parts['path']) && $parts['path'] !== '') {
        $segments = array_values(array_filter(explode('/', $parts['path']), 'strlen'));
        if (!empty($segments)) {
            $candidate = end($segments);
        } else {
            if (!empty($parts['host'])) {
                $candidate = $parts['host'];
            }
        }
    } else {
        if (preg_match('/^[A-Za-z0-9._-]+$/', $raw)) {
            $candidate = $raw;
        }
    }

    $candidate = preg_replace('/[^A-Za-z0-9._-]/', '', $candidate);
    if ($candidate === '' || $candidate === '.' || $candidate === '..') {
        $candidate = 'qrcode';
    }
    return $candidate;
}

if (isset($_GET['data'])) {
    $data = $_GET['data'];

    // Create QR code
    $qr = QrCode::create($data);
    $pngWriter = new PngWriter();
    $result = $pngWriter->write($qr); // default PNG render for preview and PNG download

    // Handle downloads with explicit format parameter (jpeg|png|svg)
    if (isset($_GET['download'])) {
        $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'jpeg'; // default auto-download is JPEG
        $filenameBase = derive_filename_base($_GET['data']);

        if ($format === 'svg') {
            $svgWriter = new SvgWriter();
            $svgResult = $svgWriter->write($qr);
            $svgData = $svgResult->getString();
            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '.svg"');
            echo $svgData;
            exit;
        }

        if ($format === 'png') {
            $pngData = $result->getString();
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '.png"');
            echo $pngData;
            exit;
        }

        // Default or explicit JPEG
        $pngData = $result->getString();
        $im = imagecreatefromstring($pngData);
        if ($im === false) {
            // Fallback to PNG if conversion fails
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '.png"');
            echo $pngData;
            exit;
        }
        $width = imagesx($im);
        $height = imagesy($im);
        $truecolor = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($truecolor, 255, 255, 255);
        imagefilledrectangle($truecolor, 0, 0, $width, $height, $white);
        imagecopy($truecolor, $im, 0, 0, 0, 0, $width, $height);

        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="' . $filenameBase . '.jpeg"');
        imagejpeg($truecolor, null, 90);

        imagedestroy($im);
        imagedestroy($truecolor);
        exit;
    }

    // For on-page preview (PNG)
    $qrImageData = base64_encode($result->getString());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Code Generator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/QR/reshot-icon-label-qr-code-CWA5ZSLBFT.svg">
    <link rel="icon" type="image/x-icon" href="/QR/reshot-icon-label-qr-code-CWA5ZSLBFT.svg">
    <link rel="shortcut icon" href="/QR/reshot-icon-label-qr-code-CWA5ZSLBFT.svg">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            transition: background 0.3s;
        }
        .main-content {
            display: flex;
            gap: 48px;
            background: rgba(255,255,255,0.18);
            border-radius: 32px;
            box-shadow: 0 12px 48px 0 rgba(31, 38, 135, 0.13), 0 1.5px 8px 0 rgba(31,38,135,0.08);
            padding: 56px 64px;
            align-items: stretch;
            justify-content: center;
            max-width: 1100px;
            width: 100%;
        }
        .container {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
            padding: 48px 40px 40px 40px;
            max-width: 420px;
            min-width: 340px;
            width: 100%;
            text-align: center;
            transition: background 0.3s, color 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .qr-preview {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
            padding: 48px 32px 40px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 320px;
            min-height: 320px;
            max-width: 420px;
        }
        .qr-preview img {
            width: 220px;
            height: 220px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 2px 12px 0 rgba(31,38,135,0.10);
        }
        h2 {
            margin-bottom: 32px;
            color: #333;
            letter-spacing: 1px;
            font-size: 2rem;
            font-weight: 700;
            transition: color 0.3s;
        }
        label {
            font-size: 1.1rem;
            color: #555;
            transition: color 0.3s;
        }
        input[type="text"] {
            width: 100%;
            padding: 16px 12px;
            margin-top: 12px;
            border: 1.5px solid #bdbdbd;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: border-color 0.2s, background 0.3s, color 0.3s;
            background: #fafafa;
            color: #222;
        }
        input[type="text"]:focus {
            border-color: #74ebd5;
            outline: none;
        }
        .button-row {
            display: flex;
            gap: 16px;
            margin-top: 28px;
            flex-wrap: wrap;
        }
        .btn, .download-btn {
            flex: 1;
            padding: 16px;
            background: linear-gradient(90deg, #74ebd5 0%, #ACB6E5 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(31, 38, 135, 0.08);
            transition: background 0.2s;
            text-decoration: none;
            text-align: center;
            min-width: 140px;
        }
        .btn:hover, .download-btn:hover {
            background: linear-gradient(90deg, #ACB6E5 0%, #74ebd5 100%);
        }
        .format-links {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: space-between;
        }
        .format-link {
            flex: 1;
            background: #fff;
            border: 1.5px solid #bdbdbd;
            color: #333;
            border-radius: 10px;
            padding: 10px 12px;
            text-align: center;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .format-link:hover {
            border-color: #74ebd5;
            color: #111;
            box-shadow: 0 2px 8px rgba(31,38,135,0.08);
        }
        .footer {
            margin-top: 32px;
            font-size: 1.05rem;
            color: #888;
            transition: color 0.3s;
        }
        @media (max-width: 1200px) {
            .main-content { padding: 32px 8px; gap: 24px; }
            .container, .qr-preview { min-width: 0; max-width: 98vw; }
        }
        @media (max-width: 900px) {
            .main-content { flex-direction: column; align-items: center; gap: 24px; padding: 24px 0; }
            .qr-preview { margin-top: 0; }
        }
        @media (max-width: 500px) {
            .container { padding: 18px 4px 12px 4px; max-width: 98vw; }
            .qr-preview { padding: 12px 2px; min-width: 0; width: 100%; max-width: 98vw; }
            h2 { font-size: 1.3rem; }
            .btn, .download-btn { font-size: 0.95rem; padding: 12px; }
            .format-link { padding: 8px; font-size: 0.9rem; }
        }
        @media (prefers-color-scheme: dark) {
            body { background: linear-gradient(135deg, #232526 0%, #414345 100%); }
            .main-content { background: rgba(35, 37, 38, 0.18); box-shadow: 0 12px 48px 0 rgba(0,0,0,0.18), 0 1.5px 8px 0 rgba(0,0,0,0.10); }
            .container, .qr-preview { background: rgba(35, 37, 38, 0.97); color: #f1f1f1; box-shadow: 0 4px 24px 0 rgba(0,0,0,0.18); }
            h2 { color: #f1f1f1; }
            label { color: #bdbdbd; }
            input[type="text"] { background: #18191a; color: #f1f1f1; border: 1.5px solid #444; }
            input[type="text"]:focus { border-color: #74ebd5; }
            .footer { color: #aaa; }
            .format-link { background: #2a2c2d; border-color: #555; color: #eee; }
            .format-link:hover { border-color: #74ebd5; color: #fff; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <h2>QR Code Generator</h2>
            <form method="GET" style="margin-bottom:0;">
                <label for="data">Enter URL or Text:</label><br>
                <input type="text" id="data" name="data" placeholder="https://example.com" required value="<?php echo isset($_GET['data']) ? htmlspecialchars($_GET['data']) : '' ?>">
                <div class="button-row">
                    <!-- Main download button: auto-download JPEG -->
                    <input type="submit" value="Show QR" class="btn">
                    <?php if ($qrImageData): ?>
                        <a class="download-btn" href="?data=<?php echo urlencode($_GET['data']); ?>&download=1">Download QR (JPEG)</a>
                        <div style="flex-basis: 100%; height: 0;"></div>
                        <div class="format-links">
                            <a class="format-link" href="?data=<?php echo urlencode($_GET['data']); ?>&download=1&format=png">PNG</a>
                            <a class="format-link" href="?data=<?php echo urlencode($_GET['data']); ?>&download=1&format=svg">SVG</a>
                            <a class="format-link" href="?data=<?php echo urlencode($_GET['data']); ?>&download=1&format=jpeg">JPEG</a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
            <div class="footer">Powered by <b>Brandspine</b></div>
        </div>
        <?php if ($qrImageData): ?>
        <div class="qr-preview">
            <img src="data:image/png;base64,<?php echo $qrImageData; ?>" alt="QR Code Preview">
            <div style="margin-top:16px;font-size:1.05rem;color:#888;">Scan or download your QR</div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
