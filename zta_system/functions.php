<?php
require_once 'config.php';

// Import PHPMailer (pastikan sudah install via composer atau manual)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Jika belum install composer, gunakan require manual
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

// Function untuk generate OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function untuk kirim email OTP via Brevo SMTP
function sendOTPEmail($email, $otp, $username) {
    $mail = new PHPMailer(true);
    
    try {
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
        $mail->addAddress($email, $username);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP Code - ZTA System';
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
                    .content { padding: 30px; }
                    .otp-box { background: #f4f4f4; padding: 20px; text-align: center; font-size: 36px; font-weight: bold; letter-spacing: 8px; margin: 25px 0; border-radius: 8px; color: #667eea; }
                    .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
                    .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; border-top: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0;'>🛡️ ZTA System</h1>
                        <p style='margin: 10px 0 0 0;'>Zero Trust Architecture</p>
                    </div>
                    <div class='content'>
                        <h2 style='color: #333;'>Hello, <strong>$username</strong>!</h2>
                        <p style='color: #666; line-height: 1.6;'>
                            You have requested to login to your account. Please use the following OTP code to complete your login:
                        </p>
                        <div class='otp-box'>$otp</div>
                        <div class='warning'>
                            <strong>⚠️ Important:</strong>
                            <ul style='margin: 10px 0; padding-left: 20px;'>
                                <li>This code will expire in <strong>5 minutes</strong></li>
                                <li>Do not share this code with anyone</li>
                                <li>If you didn't request this code, please ignore this email</li>
                            </ul>
                        </div>
                        <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                            This is an automated security verification from ZTA System.
                        </p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message, please do not reply.</p>
                        <p>&copy; " . date('Y') . " ZTA System. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Your OTP code is: $otp\n\nThis code will expire in 5 minutes.\nIf you didn't request this code, please ignore this email.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Function untuk save OTP ke database
function saveOTP($userId, $otpCode) {
    global $pdo;
    
    // Hapus OTP lama yang belum digunakan
    $deleteStmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ? AND is_used = 0");
    $deleteStmt->execute([$userId]);
    
    $expiresAt = date('Y-m-d H:i:s', time() + OTP_EXPIRY);
    
    $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
    return $stmt->execute([$userId, $otpCode, $expiresAt]);
}

// Function untuk verify OTP
function verifyOTP($userId, $otpCode) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM otp_codes 
        WHERE user_id = ? AND otp_code = ? 
        AND is_used = 0 AND expires_at > NOW() 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$userId, $otpCode]);
    $otp = $stmt->fetch();
    
    if ($otp) {
        $updateStmt = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
        $updateStmt->execute([$otp['id']]);
        return true;
    }
    
    return false;
}

// Function untuk verify reCAPTCHA
function verifyRecaptcha($token) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $result = json_decode($response);
    
    return $result->success;
}

// Function untuk cek login attempts
function checkLoginAttempts($email, $ip) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempts FROM login_attempts 
        WHERE email = ? AND ip_address = ? 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
        AND success = 0
    ");
    $stmt->execute([$email, $ip, LOCKOUT_TIME]);
    $result = $stmt->fetch();
    
    return $result['attempts'] < MAX_LOGIN_ATTEMPTS;
}

// Function untuk log login attempt
function logLoginAttempt($email, $ip, $success) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $ip, $success ? 1 : 0]);
}

// Function untuk create session
function createSession($userId) {
    global $pdo;
    
    $token = bin2hex(random_bytes(32));
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    
    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$userId, $token, $ip, $userAgent, $expiresAt])) {
        $_SESSION['session_token'] = $token;
        $_SESSION['user_id'] = $userId;
        return true;
    }
    
    return false;
}

// Function untuk verify session (ZTA)
function verifySession() {
    global $pdo;
    
    if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM user_sessions 
        WHERE session_token = ? AND user_id = ? 
        AND is_active = 1 AND expires_at > NOW()
    ");
    $stmt->execute([$_SESSION['session_token'], $_SESSION['user_id']]);
    $session = $stmt->fetch();
    
    if ($session) {
        // Update last activity
        $updateStmt = $pdo->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE id = ?");
        $updateStmt->execute([$session['id']]);
        return true;
    }
    
    return false;
}

// Function untuk logout
function logout() {
    global $pdo;
    
    if (isset($_SESSION['session_token'])) {
        $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    }
    
    session_destroy();
    return true;
}

// Function untuk sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>