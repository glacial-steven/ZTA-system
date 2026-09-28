<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'zta_system');

// Brevo SMTP Configuration
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587); // atau gunakan 465 untuk SSL
define('SMTP_USERNAME', '95a844004@smtp-brevo.com'); // Login SMTP Brevo Anda
define('SMTP_PASSWORD', 'bskyZWQquIQ2vdK'); // Password/Key SMTP Brevo
define('SMTP_FROM_EMAIL', 'yeregurning134@gmail.com'); // Email pengirim yang sudah diverifikasi
define('SMTP_FROM_NAME', 'Keykey');

// Security Configuration
define('SESSION_LIFETIME', 3600); // 1 jam
define('OTP_EXPIRY', 300); // 5 menit
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 menit

// reCAPTCHA Configuration
define('RECAPTCHA_SITE_KEY', '6LeQ2u0rAAAAAO1aH2oAqYJbqGEfmKyX9-Yynew9'); // Site key Google reCAPTCHA v2
define('RECAPTCHA_SECRET_KEY', '6LeQ2u0rAAAAAEGDrO2MIDLReosr7d5hC4tBlDrX'); // Secret key

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session
session_start();
session_regenerate_id(true);

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>