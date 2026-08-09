<?php
// reset_password.php - Complete Password Reset Tool
// ============================================================

// Direct database connection
$conn = new mysqli('localhost', 'root', '', 'suvash_portfolio');

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$message = "";
$error = "";

// Create admins table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Check if password_hash column exists
$check = $conn->query("SHOW COLUMNS FROM admins LIKE 'password_hash'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE admins ADD COLUMN password_hash VARCHAR(255) NOT NULL");
}

// Delete existing admin and insert new one
$conn->query("DELETE FROM admins WHERE username = 'admin'");

// Insert new admin with password: Admin@123
$hashed = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO admins (name, username, password_hash, email) VALUES (?, ?, ?, ?)");
$name = "Suvash Baskota";
$username = "admin";
$email = "admin@portfolio.com";
$stmt->bind_param("ssss", $name, $username, $hashed, $email);

if ($stmt->execute()) {
    $message = "✅ Admin created successfully!<br>";
    $message .= "🔑 Username: <strong>admin</strong><br>";
    $message .= "🔑 Password: <strong>Admin@123</strong><br>";
    $message .= "🔐 Hash: <code>$hashed</code><br>";
    $message .= "<br><a href='admin/login.php' style='display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;'>Go to Login →</a>";
} else {
    $error = "❌ Failed to create admin: " . $conn->error;
}
$stmt->close();

// Show all admins
$admins = $conn->query("SELECT id, username, password_hash FROM admins");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .container{background:white;padding:40px;border-radius:15px;box-shadow:0 20px 60px rgba(0,0,0,0.3);width:100%;max-width:500px;}
        h2{text-align:center;margin-bottom:10px;color:#333;}
        .success{background:#d4edda;color:#155724;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #c3e6cb;line-height:1.8;}
        .error{background:#f8d7da;color:#721c24;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #f5c6cb;}
        .info{background:#f8f9fa;padding:15px;border-radius:8px;margin-top:20px;font-size:13px;border:1px solid #e1e5e9;}
        .info strong{color:#333;}
        table{width:100%;border-collapse:collapse;margin-top:15px;font-size:13px;}
        th,td{padding:8px;text-align:left;border-bottom:1px solid #eee;}
        th{background:#f8f9fa;}
        code{background:#f4f4f4;padding:2px 6px;border-radius:4px;font-size:12px;word-break:break-all;}
    </style>
</head>
<body>
    <div class="container">
        <h2>🔑 Password Reset Tool</h2>
        
        <?php if(!empty($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="info">
            <strong>📋 Current Admins:</strong>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Password Hash</th>
                </tr>
                <?php while($row = $admins->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo $row['username']; ?></strong></td>
                    <td><code><?php echo substr($row['password_hash'], 0, 30) . '...'; ?></code></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
        <div class="info" style="margin-top:15px;">
            💡 <strong>Login Credentials:</strong><br>
            Username: <strong>admin</strong><br>
            Password: <strong>Admin@123</strong>
        </div>
    </div>
</body>
</html>