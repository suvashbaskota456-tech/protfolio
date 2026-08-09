<?php
// admin/upload-profile.php - Complete Profile Photo Upload System
// ================================================================

require_once __DIR__ . '/../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$messageType = "";

// Get current admin info
$adminInfo = getAdminInfo($conn);

// Get current profile photo
$currentPhoto = 'profile.jpg';
if ($adminInfo && !empty($adminInfo['profile_image'])) {
    $currentPhoto = $adminInfo['profile_image'];
}

// Check if photo exists
$photoPath = __DIR__ . '/../uploads/' . $currentPhoto;
if (!file_exists($photoPath)) {
    $currentPhoto = 'default.jpg';
}

// ============================================================
// HANDLE PHOTO UPLOAD
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_image'])) {
    
    $file = $_FILES['profile_image'];
    
    // Check if file was uploaded without errors
    if ($file['error'] == 0) {
        
        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $filename = $file['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $file['size'];
        
        // Check file type
        if (in_array($filetype, $allowed)) {
            
            // Check file size (max 5MB)
            if ($filesize <= 5 * 1024 * 1024) {
                
                // Create uploads directory if not exists
                $target_dir = __DIR__ . '/../uploads/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                // Generate unique filename
                $new_filename = 'profile.' . $filetype;
                $target_file = $target_dir . $new_filename;
                
                // Delete old profile image if exists and not default
                if (file_exists($target_file) && $target_file != $target_dir . 'default.jpg') {
                    unlink($target_file);
                }
                
                // Also delete old profile images with different extensions
                $old_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
                foreach ($old_extensions as $ext) {
                    $old_file = $target_dir . 'profile.' . $ext;
                    if (file_exists($old_file) && $old_file != $target_file) {
                        unlink($old_file);
                    }
                }
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    
                    // Update database
                    $stmt = $conn->prepare("UPDATE admins SET profile_image = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_filename, $_SESSION['admin_id']);
                    
                    if ($stmt->execute()) {
                        $message = "✅ Profile photo updated successfully!";
                        $messageType = "success";
                        $currentPhoto = $new_filename;
                        // Refresh admin info
                        $adminInfo = getAdminInfo($conn);
                    } else {
                        $error = "❌ Failed to update database: " . $conn->error;
                        $messageType = "error";
                    }
                    $stmt->close();
                    
                } else {
                    $error = "❌ Failed to upload file. Please check folder permissions.";
                    $messageType = "error";
                }
                
            } else {
                $error = "❌ File is too large. Maximum size is 5MB.";
                $messageType = "error";
            }
            
        } else {
            $error = "❌ Invalid file type. Allowed: JPG, PNG, GIF, WEBP, BMP";
            $messageType = "error";
        }
        
    } else {
        $error = "❌ File upload error. Please try again.";
        $messageType = "error";
    }
}

// ============================================================
// HANDLE DELETE PHOTO
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $photoToDelete = __DIR__ . '/../uploads/' . $currentPhoto;
    if (file_exists($photoToDelete) && $currentPhoto != 'default.jpg') {
        unlink($photoToDelete);
        $stmt = $conn->prepare("UPDATE admins SET profile_image = NULL WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $stmt->close();
        $message = "✅ Profile photo deleted successfully!";
        $messageType = "success";
        $currentPhoto = 'default.jpg';
        $adminInfo = getAdminInfo($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Photo - Admin</title>
    <style>
        /* ============================================================
           STYLES
           ============================================================ */
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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 550px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header .icon {
            font-size: 50px;
            display: block;
            margin-bottom: 10px;
        }
        
        .header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #888;
            font-size: 14px;
        }
        
        /* ===== PHOTO PREVIEW ===== */
        .photo-preview {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .photo-preview .photo-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .photo-preview img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .photo-preview img:hover {
            transform: scale(1.03);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
        }
        
        .photo-preview .photo-label {
            display: block;
            margin-top: 10px;
            color: #888;
            font-size: 13px;
        }
        
        .photo-preview .photo-label strong {
            color: #333;
        }
        
        /* ===== ALERTS ===== */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid transparent;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        
        /* ===== FORM ===== */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label i {
            color: #667eea;
            margin-right: 6px;
        }
        
        .form-group input[type="file"] {
            width: 100%;
            padding: 14px;
            border: 2px dashed #e1e5e9;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-size: 14px;
        }
        
        .form-group input[type="file"]:hover {
            border-color: #667eea;
            background: #f0f1ff;
        }
        
        .form-group input[type="file"]::-webkit-file-upload-button {
            padding: 8px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .form-group input[type="file"]::-webkit-file-upload-button:hover {
            background: #5a52d5;
        }
        
        .file-info {
            font-size: 12px;
            color: #888;
            margin-top: 8px;
        }
        
        .file-info i {
            margin-right: 4px;
        }
        
        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
            width: auto;
        }
        
        /* ===== BUTTON GROUP ===== */
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .btn-group .btn {
            flex: 1;
            min-width: 120px;
        }
        
        /* ===== INFO BOX ===== */
        .info-box {
            margin-top: 20px;
            padding: 16px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e1e5e9;
        }
        
        .info-box h4 {
            color: #555;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        
        .info-box ul li {
            padding: 3px 0;
            color: #888;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box ul li i {
            color: #667eea;
            font-size: 12px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }
            
            .photo-preview img {
                width: 140px;
                height: 140px;
            }
            
            .header h2 {
                font-size: 22px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ===== HEADER ===== -->
        <div class="header">
            <span class="icon">📸</span>
            <h2>Upload Profile Photo</h2>
            <p>Update your profile picture</p>
        </div>
        
        <!-- ===== MESSAGES ===== -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- ===== CURRENT PHOTO PREVIEW ===== -->
        <div class="photo-preview">
            <div class="photo-wrapper">
                <img src="../uploads/<?php echo $currentPhoto; ?>" alt="Profile Photo" id="photoPreview">
            </div>
            <span class="photo-label">
                Current: <strong><?php echo $currentPhoto; ?></strong>
            </span>
        </div>
        
        <!-- ===== UPLOAD FORM ===== -->
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="form-group">
                <label><i class="fas fa-image"></i> Choose Photo</label>
                <input type="file" name="profile_image" accept="image/*" required id="photoInput">
                <div class="file-info">
                    <i class="fas fa-info-circle"></i>
                    Allowed: JPG, PNG, GIF, WEBP, BMP | Max: 5MB
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="uploadBtn">
                <i class="fas fa-upload"></i> Upload Photo
            </button>
        </form>
        
        <!-- ===== ACTION BUTTONS ===== -->
        <div class="btn-group">
            <?php if ($currentPhoto != 'default.jpg'): ?>
                <a href="?action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this photo?')">
                    <i class="fas fa-trash"></i> Delete Photo
                </a>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <!-- ===== INFO BOX ===== -->
        <div class="info-box">
            <h4>💡 Tips for Best Photo</h4>
            <ul>
                <li><i class="fas fa-check-circle"></i> Use a clear, well-lit photo</li>
                <li><i class="fas fa-check-circle"></i> Recommended size: <strong>400x400 pixels</strong></li>
                <li><i class="fas fa-check-circle"></i> Square photos work best</li>
                <li><i class="fas fa-check-circle"></i> Professional headshot preferred</li>
            </ul>
        </div>
    </div>
    
    <!-- ============================================================
    JAVASCRIPT - Live Preview
    ============================================================ -->
    <script>
        // Live photo preview
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Show loading state on submit
        document.getElementById('uploadForm').addEventListener('submit', function() {
            const btn = document.getElementById('uploadBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            btn.disabled = true;
        });
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</body>
</html>