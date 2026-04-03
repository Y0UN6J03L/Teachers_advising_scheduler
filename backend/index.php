<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>MetaV Backend API</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .status { padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>MetaV Scheduling Backend API</h1>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="status success">
            <h3>✅ Logged in as <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo $_SESSION['role']; ?>)</h3>
            <a href="?logout=1">Logout</a>
        </div>
    <?php else: ?>
        <div class="status">Not logged in</div>
    <?php endif; ?>
    
    <h2>Endpoints (test with Postman/curl)</h2>
    <ul>
        <li><strong>POST /backend/api/auth.php</strong> - {"action":"register", "email":"...", "password":"...", "full_name":"...", "role":"student"}</li>
        <li><strong>POST /backend/api/auth.php</strong> - {"action":"login", "email":"...", "password":"..."}</li>
        <li><strong>GET /backend/api/users.php</strong> - Admin: List users</li>
        <li><strong>POST /backend/api/users.php</strong> - Admin: Create user</li>
        <li><strong>GET /backend/api/schedules.php</strong> - List schedules</li>
        <li><strong>POST /backend/api/schedules.php</strong> - Teacher: Create schedule</li>
        <li><strong>GET /backend/api/requests.php</strong> - List requests</li>
        <li><strong>POST /backend/api/requests.php</strong> - Student: Create request</li>
    </ul>
    
    <?php
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit;
    }
    ?>
    
    <h2>Setup</h2>
    <ol>
        <li>Start XAMPP (Apache + MySQL)</li>
        <li>Import backend/schema.sql to phpMyAdmin</li>
        <li>Test API endpoints above</li>
        <li>Update frontend JS to call /backend/api/*</li>
    </ol>
</body>
</html>

