<?php
// File untuk test koneksi SMTP Brevo
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

echo "<h2>Testing Brevo SMTP Connection</h2>";
echo "<hr>";

// Tampilkan konfigurasi (tanpa password)
echo "<h3>SMTP Configuration:</h3>";
echo "Host: " . SMTP_HOST . "<br>";
echo "Port: " . SMTP_PORT . "<br>";
echo "Username: " . SMTP_USERNAME . "<br>";
echo "From Email: " . SMTP_FROM_EMAIL . "<br>";
echo "From Name: " . SMTP_FROM_NAME . "<br>";
echo "<hr>";

// Test kirim email
$mail = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = 2; // 0 = off, 1 = client messages, 2 = client and server messages
    $mail->Debugoutput = 'html';
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    
    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    
    // GANTI EMAIL INI DENGAN EMAIL ANDA UNTUK TEST
    $testEmail = 'rebornerror08@gmail.com'; // <<< GANTI INI
    $mail->addAddress($testEmail, 'Test User');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from ZTA System';
    $mail->Body = '<h1>Test Email</h1><p>If you receive this email, your SMTP configuration is working correctly!</p>';
    $mail->AltBody = 'Test Email - If you receive this, SMTP is working!';
    
    echo "<h3>Sending test email to: $testEmail</h3>";
    $mail->send();
    echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px; color: #155724;'>";
    echo "<strong>✓ SUCCESS!</strong> Email has been sent successfully!";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px; color: #721c24;'>";
    echo "<strong>✗ ERROR!</strong><br>";
    echo "Message could not be sent.<br>";
    echo "Mailer Error: {$mail->ErrorInfo}";
    echo "</div>";
}

echo "<hr>";
echo "<h3>Common Issues & Solutions:</h3>";
echo "<ul>";
echo "<li><strong>SMTP connect() failed:</strong> Cek firewall, antivirus, atau koneksi internet</li>";
echo "<li><strong>Authentication failed:</strong> Cek SMTP_USERNAME dan SMTP_PASSWORD</li>";
echo "<li><strong>Invalid address:</strong> Email pengirim belum diverifikasi di Brevo</li>";
echo "<li><strong>Unable to find the socket transport ssl:</strong> Aktifkan extension=openssl di php.ini</li>";
echo "</ul>";
?>