<?php
require_once 'functions.php';

// Verify session (ZTA)
if (!verifySession()) {
    header('Location: login.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get active sessions
$stmt = $pdo->prepare("
    SELECT * FROM user_sessions 
    WHERE user_id = ? AND is_active = 1 
    ORDER BY last_activity DESC
");
$stmt->execute([$_SESSION['user_id']]);
$sessions = $stmt->fetchAll();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    logout();
    header('Location: login.php');
    exit;
}

// Handle terminate session
if (isset($_GET['action']) && $_GET['action'] == 'terminate' && isset($_GET['session_id'])) {
    $sessionId = (int)$_GET['session_id'];
    $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE id = ? AND user_id = ?");
    $stmt->execute([$sessionId, $_SESSION['user_id']]);
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ZTA System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            font-size: 24px;
        }
        .navbar-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            color: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .sessions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sessions-table th,
        .sessions-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .sessions-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        .sessions-table tr:hover {
            background: #f8f9fa;
        }
        .btn-terminate {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-terminate:hover {
            background: #c82333;
        }
        .current-session {
            background: #e7f3ff;
        }
        .security-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .security-info h3 {
            color: #0c5460;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .security-info p {
            color: #0c5460;
            font-size: 14px;
            line-height: 1.6;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-content">
            <h1>🛡️ ZTA Dashboard</h1>
            <div class="navbar-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($sessions); ?></div>
                <div class="stat-label">Active Sessions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php echo $user['is_verified'] ? '✓' : '✗'; ?>
                </div>
                <div class="stat-label">Account Status</div>
            </div>
        </div>
        
        <div class="card">
            <h2>Account Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Verification Status</div>
                    <div class="info-value">
                        <?php if ($user['is_verified']): ?>
                            <span class="badge badge-success">✓ Verified</span>
                        <?php else: ?>
                            <span class="badge badge-warning">⚠ Not Verified</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Member Since</div>
                    <div class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Active Sessions</h2>
            <p style="color: #666; margin-bottom: 15px;">Manage your active sessions across different devices</p>
            
            <table class="sessions-table">
                <thead>
                    <tr>
                        <th>Device / Browser</th>
                        <th>IP Address</th>
                        <th>Last Activity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr class="<?php echo ($session['session_token'] == $_SESSION['session_token']) ? 'current-session' : ''; ?>">
                            <td>
                                <?php 
                                $ua = $session['user_agent'];
                                if (strpos($ua, 'Chrome') !== false) echo '🌐 Chrome';
                                elseif (strpos($ua, 'Firefox') !== false) echo '🦊 Firefox';
                                elseif (strpos($ua, 'Safari') !== false) echo '🧭 Safari';
                                elseif (strpos($ua, 'Edge') !== false) echo '🔷 Edge';
                                else echo '💻 Browser';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($session['last_activity'])); ?></td>
                            <td>
                                <?php if ($session['session_token'] == $_SESSION['session_token']): ?>
                                    <span class="badge badge-success">Current</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($session['session_token'] != $_SESSION['session_token']): ?>
                                    <a href="?action=terminate&session_id=<?php echo $session['id']; ?>" 
                                       class="btn-terminate"
                                       onclick="return confirm('Terminate this session?')">
                                        Terminate
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="security-info">
                <h3>🔒 Zero Trust Architecture</h3>
                <p>
                    Your account is protected with Zero Trust Architecture principles. Every login requires OTP verification,
                    and sessions are continuously monitored. If you notice any suspicious activity, terminate the session immediately
                    and change your password.
                </p>
            </div>
        </div>
    </div>
</body>
</html>