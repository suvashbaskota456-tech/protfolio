<?php
// admin/login.php - Updated with redirect to portfolio
require_once __DIR__ . '/../config.php';

// If already logged in, go to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, name, username, password_hash FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_username'] = $row['username'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .login-box{background:white;padding:40px;border-radius:15px;box-shadow:0 20px 60px rgba(0,0,0,0.3);width:100%;max-width:400px;}
        h2{text-align:center;margin-bottom:10px;color:#333;}
        .subtitle{text-align:center;color:#888;margin-bottom:30px;font-size:14px;}
        .back-link{text-align:center;margin-top:15px;font-size:13px;}
        .back-link a{color:#667eea;text-decoration:none;}
        .back-link a:hover{text-decoration:underline;}
        .form-group{margin-bottom:20px;}
        input{width:100%;padding:12px 15px;border:2px solid #e1e5e9;border-radius:8px;font-size:15px;transition:all 0.3s;}
        input:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 4px rgba(102,126,234,0.1);}
        .btn-login{width:100%;padding:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.3s;}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(102,126,234,0.4);}
        .error{color:#dc3545;background:#f8d7da;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;border:1px solid #f5c6cb;}
        .success{color:#155724;background:#d4edda;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;border:1px solid #c3e6cb;}
        .info{margin-top:20px;padding:12px;background:#f8f9fa;border-radius:8px;text-align:center;font-size:13px;color:#666;border:1px solid #e1e5e9;}
        .info strong{color:#333;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Admin Login</h2>
        <p class="subtitle">Enter credentials to manage portfolio</p>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="👤 Username" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="🔑 Password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="info">
            💡 Default: <strong>admin</strong> / <strong>Admin@123</strong>
        </div>
        
        <div class="back-link">
            ← <a href="../index.php">Back to Portfolio</a>
        </div>
    </div>
</body>
</html>