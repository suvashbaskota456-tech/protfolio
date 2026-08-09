<?php
// admin/add-service.php - Add New Service
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    
    if (empty($title) || empty($description)) {
        $error = "Title and Description are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO services (title, description, icon) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $icon);
        
        if ($stmt->execute()) {
            $message = "✅ Service added successfully!";
        } else {
            $error = "❌ Failed to add service: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Service</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f7fa;padding:20px;}
        .container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);}
        h2{color:#333;margin-bottom:20px;}
        .form-group{margin-bottom:20px;}
        label{display:block;margin-bottom:5px;color:#555;font-weight:600;}
        input,textarea{width:100%;padding:10px 12px;border:2px solid #e1e5e9;border-radius:8px;font-size:14px;}
        input:focus,textarea:focus{outline:none;border-color:#667eea;}
        textarea{resize:vertical;min-height:80px;}
        .btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#ffc107,#f39c12);color:#333;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(255,193,7,0.4);}
        .btn-back{display:inline-block;padding:10px 20px;background:#6c757d;color:white;border-radius:8px;text-decoration:none;margin-top:10px;}
        .success{background:#d4edda;color:#155724;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #c3e6cb;}
        .error{background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #f5c6cb;}
    </style>
</head>
<body>
    <div class="container">
        <h2>🛠️ Add New Service</h2>
        
        <?php if(!empty($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Service Title *</label>
                <input type="text" name="title" placeholder="e.g., Web Development" required>
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" placeholder="Describe your service..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Icon (Font Awesome class)</label>
                <input type="text" name="icon" placeholder="e.g., fa-code, fa-paint-brush">
            </div>
            
            <button type="submit" class="btn-submit">➕ Add Service</button>
        </form>
        
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</body>
</html>