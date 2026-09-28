<?php
require_once 'functions.php';

// Cek apakah ada temp user id
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['temp_email'])) {
    header('Location: login.php');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'login';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otpCode = sanitize($_POST['otp']);
    $userId = $_SESSION['temp_user_id'];
    
    if (empty($otpCode)) {
        $error = 'Please enter the OTP code.';
    } elseif (strlen($otpCode) != 6) {
        $error = 'OTP must be 6 digits.';
    } else {
        // Verify OTP
        if (verifyOTP($userId, $otpCode, $type)) {
            if ($type == 'registration') {
                // Update user sebagai verified
                $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                
                $success = 'Registration successful! You can now login.';
                unset($_SESSION['temp_user_id']);
                unset($_SESSION['temp_email']);
                
                // Redirect ke login setelah 2 detik
                header('refresh:2;url=login.php');
            } elseif ($type == 'login') {
                // Create session untuk user
                if (createSession($userId)) {
                    unset($_SESSION['temp_user_id']);
                    unset($_SESSION['temp_email']);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Failed to create session. Please try again.';
                }
            }
        } else {
            $error = 'Invalid or expired OTP code. Please try again.';
        }
    }
}

// Function untuk resend OTP
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    $userId = $_SESSION['temp_user_id'];
    $email = $_SESSION['temp_email'];
    
    // Get username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Generate dan kirim OTP baru
    $otp = generateOTP();
    
    if (saveOTP($userId, $otp, $type) && sendOTPEmail($email, $otp, $user['username'])) {
        $success = 'New OTP has been sent to your email.';
    } else {
        $error = 'Failed to send OTP. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - ZTA System</title>
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
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            text-align: center;
        }
        .email-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            color: #667eea;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            text-align: center;
        }
        input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .resend-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .resend-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .resend-link a:hover {
            text-decoration: underline;
        }
        .timer {
            text-align: center;
            margin-top: 15px;
            color: #999;
            font-size: 14px;
        }
        .timer span {
            font-weight: bold;
            color: #667eea;
        }
        .icon-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .icon-container svg {
            width: 64px;
            height: 64px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        
        <h1>Verify OTP</h1>
        <p class="subtitle">Enter the 6-digit code sent to your email</p>
        
        <div class="email-display">
            <?php echo $_SESSION['temp_email']; ?>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Enter OTP Code</label>
                <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" required placeholder="000000" autofocus>
            </div>
            
            <button type="submit" class="btn">Verify OTP</button>
        </form>
        
        <div class="timer">
            Code expires in <span id="countdown">5:00</span>
        </div>
        
        <div class="resend-link">
            Didn't receive the code? <a href="?type=<?php echo $type; ?>&resend=1">Resend OTP</a>
        </div>
    </div>
    
    <script>
        // Countdown timer
        let timeLeft = 300; // 5 minutes in seconds
        
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('countdown').textContent = 
                minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            
            if (timeLeft > 0) {
                timeLeft--;
                setTimeout(updateCountdown, 1000);
            } else {
                document.getElementById('countdown').textContent = 'EXPIRED';
                document.getElementById('countdown').style.color = '#c33';
            }
        }
        
        updateCountdown();
        
        // Auto-submit when 6 digits entered
        const otpInput = document.querySelector('input[name="otp"]');
        otpInput.addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Validasi hanya angka
                if (/^\d{6}$/.test(this.value)) {
                    this.form.submit();
                }
            }
        });
    </script>
</body>
</html>