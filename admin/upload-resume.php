<?php
// admin/upload-resume.php - Complete Resume Upload System
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$messageType = "";

// Check if resume exists
$resumePath = __DIR__ . '/../uploads/resume.pdf';
$resumeExists = file_exists($resumePath);
$resumeSize = $resumeExists ? filesize($resumePath) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['resume'])) {
    $file = $_FILES['resume'];
    
    if ($file['error'] == 0) {
        $filetype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filesize = $file['size'];
        
        if ($filetype === 'pdf') {
            if ($filesize <= 10 * 1024 * 1024) {
                $target_dir = __DIR__ . '/../uploads/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $target_file = $target_dir . 'resume.pdf';
                
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $message = "✅ Resume uploaded successfully!";
                    $messageType = "success";
                    $resumeExists = true;
                    $resumeSize = filesize($target_file);
                } else {
                    $error = "❌ Failed to upload resume.";
                    $messageType = "error";
                }
            } else {
                $error = "❌ File too large. Max 10MB.";
                $messageType = "error";
            }
        } else {
            $error = "❌ Invalid file type. Only PDF allowed.";
            $messageType = "error";
        }
    } else {
        $error = "❌ File upload error. Please try again.";
        $messageType = "error";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    if ($resumeExists) {
        unlink($resumePath);
        $resumeExists = false;
        $resumeSize = 0;
        $message = "✅ Resume deleted successfully!";
        $messageType = "success";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Resume</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .container{background:white;padding:40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);width:100%;max-width:500px;}
        .header{text-align:center;margin-bottom:30px;}
        .header .icon{font-size:50px;display:block;margin-bottom:10px;}
        .header h2{font-size:26px;color:#333;}
        .header p{color:#888;font-size:14px;}
        .status{text-align:center;padding:20px;border-radius:12px;margin:20px 0;border:2px dashed #e1e5e9;}
        .status .file-icon{font-size:48px;display:block;margin-bottom:10px;}
        .status .file-name{font-weight:600;color:#333;font-size:16px;}
        .status .file-size{color:#888;font-size:13px;margin-top:4px;}
        .status .file-status{display:inline-block;padding:4px 16px;border-radius:50px;font-size:12px;font-weight:600;margin-top:10px;}
        .status .file-status.uploaded{background:#d4edda;color:#155724;}
        .status .file-status.missing{background:#f8d7da;color:#721c24;}
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .form-group{margin-bottom:20px;}
        .form-group label{display:block;margin-bottom:8px;color:#555;font-weight:600;font-size:14px;}
        .form-group input[type="file"]{width:100%;padding:14px;border:2px dashed #e1e5e9;border-radius:10px;cursor:pointer;background:#f8f9fa;font-size:14px;transition:all 0.3s;}
        .form-group input[type="file"]:hover{border-color:#667eea;background:#f0f1ff;}
        .form-group input[type="file"]::-webkit-file-upload-button{padding:8px 20px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;margin-right:10px;transition:all 0.3s;}
        .form-group input[type="file"]::-webkit-file-upload-button:hover{background:#5a52d5;}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 28px;border-radius:10px;font-weight:600;font-size:15px;cursor:pointer;border:none;transition:all 0.3s;text-decoration:none;width:100%;font-family:inherit;}
        .btn-primary{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;box-shadow:0 4px 15px rgba(102,126,234,0.3);}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(102,126,234,0.4);}
        .btn-danger{background:#dc3545;color:white;}
        .btn-danger:hover{background:#c82333;transform:translateY(-2px);}
        .btn-secondary{background:#6c757d;color:white;}
        .btn-secondary:hover{background:#5a6268;transform:translateY(-2px);}
        .btn-success{background:#28a745;color:white;}
        .btn-success:hover{background:#218838;transform:translateY(-2px);}
        .btn-sm{padding:8px 16px;font-size:13px;width:auto;}
        .btn-group{display:flex;gap:12px;margin-top:10px;flex-wrap:wrap;}
        .btn-group .btn{flex:1;min-width:100px;}
        .info-box{margin-top:20px;padding:16px 20px;background:#f8f9fa;border-radius:10px;border:1px solid #e1e5e9;}
        .info-box h4{color:#555;font-size:14px;margin-bottom:8px;}
        .info-box ul{list-style:none;padding:0;}
        .info-box ul li{padding:3px 0;color:#888;font-size:13px;display:flex;align-items:center;gap:8px;}
        .info-box ul li i{color:#667eea;}
        .info-box ul li strong{color:#333;}
        @media(max-width:600px){.container{padding:25px;}.btn-group{flex-direction:column;}.btn-group .btn{width:100%;}}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="icon">📄</span>
            <h2>Upload Resume</h2>
            <p>Upload your CV/Resume (PDF only)</p>
        </div>
        
        <?php if(!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Status -->
        <div class="status">
            <span class="file-icon"><?php echo $resumeExists ? '✅' : '📄'; ?></span>
            <div class="file-name"><?php echo $resumeExists ? 'resume.pdf' : 'No resume uploaded'; ?></div>
            <div class="file-size"><?php echo $resumeExists ? number_format($resumeSize / 1024, 1) . ' KB' : ''; ?></div>
            <span class="file-status <?php echo $resumeExists ? 'uploaded' : 'missing'; ?>">
                <?php echo $resumeExists ? '✅ Uploaded' : '❌ Not Found'; ?>
            </span>
        </div>
        
        <!-- Upload Form -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label><i class="fas fa-file-pdf"></i> Choose PDF File</label>
                <input type="file" name="resume" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Resume
            </button>
        </form>
        
        <div class="btn-group">
            <?php if ($resumeExists): ?>
                <a href="../uploads/resume.pdf" target="_blank" class="btn btn-success btn-sm">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="?action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete the resume?')">
                    <i class="fas fa-trash"></i> Delete
                </a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="info-box">
            <h4>💡 Tips</h4>
            <ul>
                <li><i class="fas fa-check-circle"></i> Only <strong>PDF</strong> files allowed</li>
                <li><i class="fas fa-check-circle"></i> Max size: <strong>10 MB</strong></li>
                <li><i class="fas fa-check-circle"></i> File name must be <strong>resume.pdf</strong></li>
                <li><i class="fas fa-check-circle"></i> Location: <strong>uploads/resume.pdf</strong></li>
            </ul>
        </div>
    </div>
</body>
</html>