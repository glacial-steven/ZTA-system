<?php
require_once 'functions.php';

// Jika sudah login, redirect ke dashboard
if (verifySession()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZTA System - Zero Trust Architecture</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 60px 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        .logo {
            font-size: 72px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 36px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 16px;
            line-height: 1.6;
        }
        .features {
            text-align: left;
            margin: 40px 0;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
        }
        .features h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .feature-item {
            display: flex;
            align-items: start;
            margin-bottom: 15px;
            gap: 12px;
        }
        .feature-icon {
            color: #667eea;
            font-size: 24px;
            flex-shrink: 0;
        }
        .feature-text {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }
        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .security-badge {
            margin-top: 40px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 10px;
            border: 2px solid #2196F3;
        }
        .security-badge h4 {
            color: #0c5460;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .security-badge p {
            color: #0c5460;
            font-size: 13px;
            line-height: 1.6;
        }
        @media (max-width: 600px) {
            .btn-group {
                flex-direction: column;
            }
            h1 {
                font-size: 28px;
            }
            .logo {
                font-size: 56px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🛡️</div>
        <h1>Welcome to ZTA System</h1>
        <p class="subtitle">
            Secure authentication system powered by Zero Trust Architecture with Email OTP verification and CAPTCHA protection
        </p>
        
        <div class="features">
            <h3>🔐 Security Features</h3>
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <div class="feature-text">
                    <strong>Two-Factor Authentication</strong><br>
                    Every login requires OTP verification via email
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <div class="feature-text">
                    <strong>CAPTCHA Protection</strong><br>
                    Google reCAPTCHA v2 prevents automated attacks
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <div class="feature-text">
                    <strong>Session Management</strong><br>
                    Monitor and control all active sessions across devices
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <div class="feature-text">
                    <strong>Brute Force Protection</strong><br>
                    Automatic lockout after multiple failed login attempts
                </div>
            </div>
        </div>
        
        <div class="btn-group">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </div>
        
        <div class="security-badge">
            <h4>🔒 Zero Trust Architecture</h4>
            <p>
                This system follows Zero Trust principles: "Never trust, always verify." 
                Every access request is authenticated, authorized, and encrypted regardless of where it originates.
            </p>
        </div>
    </div>
</body>
</html>