<?php
echo "<h2>Checking PHP & SMTP Configuration</h2>";
echo "<hr>";

// Check PHPMailer
echo "<h3>1. PHPMailer Installation</h3>";
if (file_exists('PHPMailer/src/PHPMailer.php')) {
    echo "✓ PHPMailer found<br>";
} else {
    echo "✗ PHPMailer NOT found. Please install PHPMailer first!<br>";
    echo "Download from: <a href='https://github.com/PHPMailer/PHPMailer' target='_blank'>https://github.com/PHPMailer/PHPMailer</a><br>";
}

// Check OpenSSL
echo "<h3>2. OpenSSL Extension</h3>";
if (extension_loaded('openssl')) {
    echo "✓ OpenSSL is enabled<br>";
} else {
    echo "✗ OpenSSL is NOT enabled<br>";
    echo "Enable it in php.ini: Remove semicolon from ;extension=openssl<br>";
}

// Check config file
echo "<h3>3. Config File</h3>";
if (file_exists('config.php')) {
    echo "✓ config.php found<br>";
    require_once 'config.php';
    
    echo "<h4>SMTP Settings:</h4>";
    echo "Host: " . (defined('SMTP_HOST') ? SMTP_HOST : '❌ NOT SET') . "<br>";
    echo "Port: " . (defined('SMTP_PORT') ? SMTP_PORT : '❌ NOT SET') . "<br>";
    echo "Username: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : '❌ NOT SET') . "<br>";
    echo "Password: " . (defined('SMTP_PASSWORD') && SMTP_PASSWORD != 'YOUR_BREVO_SMTP_PASSWORD' ? '✓ SET (hidden)' : '❌ NOT SET or still default') . "<br>";
    echo "From Email: " . (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '❌ NOT SET') . "<br>";
    
    // Check if still using default values
    if (SMTP_USERNAME == 'YOUR_BREVO_SMTP_LOGIN' || SMTP_PASSWORD == 'YOUR_BREVO_SMTP_PASSWORD') {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px; color: #721c24;'>";
        echo "<strong>⚠️ WARNING:</strong> You are still using default SMTP values!<br>";
        echo "Please update config.php with your actual Brevo SMTP credentials.";
        echo "</div>";
    }
} else {
    echo "✗ config.php NOT found<br>";
}

// Check PHP version
echo "<h3>4. PHP Version</h3>";
echo "Current version: " . PHP_VERSION . "<br>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✓ PHP version is compatible<br>";
} else {
    echo "⚠️ PHP version is too old. Recommended: 7.4 or higher<br>";
}

// Check internet connection
echo "<h3>5. Internet Connection</h3>";
$connected = @fsockopen("www.google.com", 80);
if ($connected) {
    echo "✓ Internet connection is working<br>";
    fclose($connected);
} else {
    echo "✗ Cannot connect to internet. Check your connection.<br>";
}

// Check SMTP connection
echo "<h3>6. SMTP Server Connection</h3>";
if (defined('SMTP_HOST') && defined('SMTP_PORT')) {
    $smtpConnected = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
    if ($smtpConnected) {
        echo "✓ Can connect to " . SMTP_HOST . ":" . SMTP_PORT . "<br>";
        fclose($smtpConnected);
    } else {
        echo "✗ Cannot connect to SMTP server<br>";
        echo "Error: $errstr ($errno)<br>";
        echo "Check firewall or antivirus settings<br>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Make sure all checks above show ✓</li>";
echo "<li>Update config.php with your actual Brevo SMTP credentials</li>";
echo "<li>Run <a href='test_smtp.php'>test_smtp.php</a> to test email sending</li>";
echo "<li>Check spam/junk folder if email doesn't arrive in inbox</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>Common Solutions:</h3>";
echo "<ul>";
echo "<li><strong>PHPMailer not found:</strong> Install via Composer or download manually</li>";
echo "<li><strong>OpenSSL not enabled:</strong> Edit php.ini, find ;extension=openssl and remove semicolon</li>";
echo "<li><strong>Cannot connect to SMTP:</strong> Check firewall, antivirus, or use different port (465 for SSL)</li>";
echo "<li><strong>Authentication failed:</strong> Verify SMTP username and password in Brevo dashboard</li>";
echo "</ul>";
?>