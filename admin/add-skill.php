<?php
// admin/add-skill.php - Add New Skill
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $percentage = intval($_POST['percentage'] ?? 0);
    $icon = trim($_POST['icon'] ?? '');
    
    if (empty($name) || $percentage <= 0 || $percentage > 100) {
        $error = "Skill name and valid percentage (1-100) are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO skills (name, category, percentage, icon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $category, $percentage, $icon);
        
        if ($stmt->execute()) {
            $message = "✅ Skill added successfully!";
        } else {
            $error = "❌ Failed to add skill: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Skill</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f7fa;padding:20px;}
        .container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);}
        h2{color:#333;margin-bottom:20px;}
        .form-group{margin-bottom:20px;}
        label{display:block;margin-bottom:5px;color:#555;font-weight:600;}
        input,select{width:100%;padding:10px 12px;border:2px solid #e1e5e9;border-radius:8px;font-size:14px;}
        input:focus,select:focus{outline:none;border-color:#667eea;}
        .btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#28a745,#20c997);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(40,167,69,0.4);}
        .btn-back{display:inline-block;padding:10px 20px;background:#6c757d;color:white;border-radius:8px;text-decoration:none;margin-top:10px;}
        .success{background:#d4edda;color:#155724;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #c3e6cb;}
        .error{background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #f5c6cb;}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
        @media(max-width:600px){.row{grid-template-columns:1fr;}}
    </style>
</head>
<body>
    <div class="container">
        <h2>💪 Add New Skill</h2>
        
        <?php if(!empty($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Skill Name *</label>
                <input type="text" name="name" placeholder="e.g., PHP, JavaScript, Leadership" required>
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Technical">Technical</option>
                    <option value="Soft Skills">Soft Skills</option>
                    <option value="Management">Management</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Percentage (1-100) *</label>
                <input type="number" name="percentage" min="1" max="100" placeholder="e.g., 85" required>
            </div>
            
            <div class="form-group">
                <label>Icon (Font Awesome class)</label>
                <input type="text" name="icon" placeholder="e.g., fa-code, fa-users">
            </div>
            
            <button type="submit" class="btn-submit">➕ Add Skill</button>
        </form>
        
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</body>
</html>