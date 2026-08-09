<?php
// admin/manage-about.php - Complete About Management
// ================================================================

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ===== GET ABOUT INFO =====
$about = getAboutInfo($conn);

// If no about data exists, create default
if (!$about) {
    $conn->query("INSERT INTO about (name, designation, location, email, phone, experience, about_text) VALUES (
        'Suvash Baskota',
        'Microfinance Officer',
        'Kalaiya, Bara, Nepal',
        'suvashbaskota456@gmail.com',
        '+977-9861173924',
        '3+ Years',
        'Experienced microfinance professional with practical exposure in branch management and internal audit.'
    )");
    $about = getAboutInfo($conn);
}

$message = "";
$error = "";
$imageMessage = "";
$cvMessage = "";

// ===== HANDLE ABOUT UPDATE =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_about'])) {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'designation' => trim($_POST['designation'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'experience' => trim($_POST['experience'] ?? ''),
        'about_text' => trim($_POST['about_text'] ?? ''),
        'facebook' => trim($_POST['facebook'] ?? ''),
        'linkedin' => trim($_POST['linkedin'] ?? ''),
        'twitter' => trim($_POST['twitter'] ?? ''),
        'youtube' => trim($_POST['youtube'] ?? ''),
        'instagram' => trim($_POST['instagram'] ?? '')
    ];
    
    if (empty($data['name']) || empty($data['about_text'])) {
        $error = "Name and About Text are required!";
    } else {
        if (updateAboutInfo($conn, $data)) {
            $message = "✅ About information updated successfully!";
            $about = getAboutInfo($conn);
        } else {
            $error = "❌ Failed to update: " . $conn->error;
        }
    }
}

// ===== HANDLE IMAGE UPLOAD =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = __DIR__ . '/../uploads/';
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filetype = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $image = 'about.' . $filetype;
            $target_file = $target_dir . $image;
            
            // Delete old image
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                if (updateAboutImage($conn, $image)) {
                    $imageMessage = "✅ Profile image updated!";
                    $about = getAboutInfo($conn);
                }
            }
        } else {
            $error = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP";
        }
    }
}

// ===== HANDLE CV UPLOAD =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_cv'])) {
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $filetype = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
        
        if ($filetype == 'pdf') {
            $target_dir = __DIR__ . '/../uploads/';
            $cv = 'resume.pdf';
            $target_file = $target_dir . $cv;
            
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target_file)) {
                if (updateAboutCV($conn, $cv)) {
                    $cvMessage = "✅ CV uploaded successfully!";
                    $about = getAboutInfo($conn);
                }
            }
        } else {
            $error = "Only PDF files are allowed!";
        }
    }
}

// ===== HANDLE DELETE CV =====
if (isset($_GET['delete_cv'])) {
    $cvPath = __DIR__ . '/../uploads/resume.pdf';
    if (file_exists($cvPath)) {
        unlink($cvPath);
    }
    updateAboutCV($conn, '');
    $about = getAboutInfo($conn);
    $cvMessage = "✅ CV deleted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f2f5;}
        
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:18px 30px;color:white;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;}
        .header h1{font-size:22px;font-weight:700;}
        .header h1 i{margin-right:10px;}
        .header-actions{display:flex;align-items:center;gap:15px;flex-wrap:wrap;}
        .btn-back{background:rgba(255,255,255,0.2);color:white;border:2px solid rgba(255,255,255,0.3);padding:8px 20px;border-radius:50px;text-decoration:none;transition:all 0.3s;font-weight:500;font-size:14px;}
        .btn-back:hover{background:white;color:#764ba2;transform:translateY(-2px);}
        .btn-logout{background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.2);padding:8px 20px;border-radius:50px;text-decoration:none;transition:all 0.3s;font-weight:500;font-size:14px;}
        .btn-logout:hover{background:#dc3545;border-color:#dc3545;transform:translateY(-2px);}
        
        .container{max-width:900px;margin:30px auto;padding:0 20px;}
        
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .alert-info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}
        
        .card{background:white;border-radius:12px;padding:25px;margin-bottom:25px;box-shadow:0 4px 15px rgba(0,0,0,0.06);}
        .card h3{font-size:17px;font-weight:700;margin-bottom:20px;color:#333;padding-bottom:10px;border-bottom:2px solid #f0f0f5;}
        .card h3 i{margin-right:8px;color:#667eea;}
        
        .form-group{margin-bottom:18px;}
        .form-group label{display:block;margin-bottom:6px;color:#555;font-weight:600;font-size:13px;}
        .form-group input,.form-group textarea{width:100%;padding:10px 14px;border:2px solid #e1e5e9;border-radius:8px;font-size:14px;transition:all 0.3s;}
        .form-group input:focus,.form-group textarea:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 4px rgba(102,126,234,0.1);}
        .form-group textarea{resize:vertical;min-height:100px;}
        .form-group .row{display:grid;grid-template-columns:1fr 1fr;gap:15px;}
        
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;border:none;transition:all 0.3s;text-decoration:none;}
        .btn-primary{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(102,126,234,0.3);}
        .btn-success{background:#28a745;color:white;}
        .btn-success:hover{background:#218838;transform:translateY(-2px);}
        .btn-danger{background:#dc3545;color:white;}
        .btn-danger:hover{background:#c82333;transform:translateY(-2px);}
        .btn-secondary{background:#6c757d;color:white;}
        .btn-secondary:hover{background:#5a6268;transform:translateY(-2px);}
        .btn-sm{padding:6px 16px;font-size:12px;}
        
        .image-preview{display:flex;align-items:center;gap:20px;padding:15px;background:#f8f9fa;border-radius:8px;}
        .image-preview img{width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #667eea;}
        .image-preview .info{flex:1;}
        .image-preview .info p{margin:2px 0;color:#888;font-size:13px;}
        .image-preview .info strong{color:#333;}
        
        .file-upload{display:flex;gap:15px;align-items:center;flex-wrap:wrap;}
        .file-upload input[type="file"]{flex:1;padding:10px;border:2px dashed #e1e5e9;border-radius:8px;cursor:pointer;}
        .file-upload input[type="file"]:hover{border-color:#667eea;}
        
        .social-links{display:grid;grid-template-columns:1fr 1fr;gap:15px;}
        
        .current-cv{display:flex;align-items:center;gap:15px;padding:15px;background:#f8f9fa;border-radius:8px;}
        .current-cv i{font-size:30px;color:#dc3545;}
        .current-cv .info{flex:1;}
        .current-cv .info p{margin:2px 0;color:#888;font-size:13px;}
        
        @media(max-width:768px){
            .header{flex-direction:column;text-align:center;}
            .form-group .row{grid-template-columns:1fr;}
            .social-links{grid-template-columns:1fr;}
            .image-preview{flex-direction:column;text-align:center;}
            .file-upload{flex-direction:column;}
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <h1><i class="fas fa-user-edit"></i> Manage About</h1>
    <div class="header-actions">
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ===== CONTAINER ===== -->
<div class="container">

    <!-- ===== ALERTS ===== -->
    <?php if(!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($imageMessage)): ?>
        <div class="alert alert-success"><?php echo $imageMessage; ?></div>
    <?php endif; ?>
    <?php if(!empty($cvMessage)): ?>
        <div class="alert alert-success"><?php echo $cvMessage; ?></div>
    <?php endif; ?>

    <!-- ===== ABOUT INFORMATION ===== -->
    <div class="card">
        <h3><i class="fas fa-user"></i> Personal Information</h3>
        <form method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($about['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Designation / Title</label>
                <input type="text" name="designation" value="<?php echo htmlspecialchars($about['designation'] ?? ''); ?>" placeholder="e.g., Microfinance Officer">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($about['location'] ?? ''); ?>" placeholder="e.g., Kalaiya, Bara, Nepal">
            </div>
            <div class="form-group">
                <div class="row">
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($about['email'] ?? ''); ?>">
                    </div>
                    <div>
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($about['phone'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Experience</label>
                <input type="text" name="experience" value="<?php echo htmlspecialchars($about['experience'] ?? ''); ?>" placeholder="e.g., 3+ Years">
            </div>
            <div class="form-group">
                <label>About Text *</label>
                <textarea name="about_text" rows="5" required><?php echo htmlspecialchars($about['about_text'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="update_about" class="btn btn-primary">
                <i class="fas fa-save"></i> Update About
            </button>
        </form>
    </div>

    <!-- ===== PROFILE IMAGE ===== -->
    <div class="card">
        <h3><i class="fas fa-image"></i> Profile Image</h3>
        <div class="image-preview">
            <img src="../uploads/<?php echo $about['profile_image'] ?? 'default.jpg'; ?>" alt="Profile Image">
            <div class="info">
                <p><strong>Current Image:</strong> <?php echo $about['profile_image'] ?? 'default.jpg'; ?></p>
                <p style="font-size:12px;color:#888;">Recommended: 400x400 pixels, Square</p>
            </div>
        </div>
        <form method="POST" enctype="multipart/form-data" style="margin-top:15px;">
            <div class="file-upload">
                <input type="file" name="profile_image" accept="image/*" required>
                <button type="submit" name="upload_image" class="btn btn-success">
                    <i class="fas fa-upload"></i> Upload Image
                </button>
            </div>
        </form>
    </div>

    <!-- ===== CV / RESUME ===== -->
    <div class="card">
        <h3><i class="fas fa-file-pdf"></i> CV / Resume</h3>
        <?php if (!empty($about['cv_file']) && file_exists(__DIR__ . '/../uploads/' . $about['cv_file'])): ?>
            <div class="current-cv">
                <i class="fas fa-file-pdf"></i>
                <div class="info">
                    <p><strong>Current CV:</strong> <?php echo $about['cv_file']; ?></p>
                    <p style="font-size:12px;color:#888;">
                        <a href="../uploads/<?php echo $about['cv_file']; ?>" target="_blank" style="color:#667eea;">View CV</a>
                    </p>
                </div>
                <a href="?delete_cv=1" class="btn btn-danger btn-sm" onclick="return confirm('Delete CV?')">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        <?php else: ?>
            <p style="color:#888;margin-bottom:15px;">No CV uploaded yet.</p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="file-upload">
                <input type="file" name="cv_file" accept=".pdf" required>
                <button type="submit" name="upload_cv" class="btn btn-success">
                    <i class="fas fa-upload"></i> Upload CV
                </button>
            </div>
            <p style="font-size:12px;color:#888;margin-top:8px;">Only PDF files allowed. Max size: 10MB</p>
        </form>
    </div>

    <!-- ===== SOCIAL MEDIA LINKS ===== -->
    <div class="card">
        <h3><i class="fas fa-share-alt"></i> Social Media Links</h3>
        <form method="POST">
            <div class="social-links">
                <div class="form-group">
                    <label><i class="fab fa-facebook" style="color:#1877f2;"></i> Facebook</label>
                    <input type="url" name="facebook" value="<?php echo htmlspecialchars($about['facebook'] ?? ''); ?>" placeholder="https://facebook.com/username">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-linkedin" style="color:#0a66c2;"></i> LinkedIn</label>
                    <input type="url" name="linkedin" value="<?php echo htmlspecialchars($about['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-twitter" style="color:#1da1f2;"></i> Twitter/X</label>
                    <input type="url" name="twitter" value="<?php echo htmlspecialchars($about['twitter'] ?? ''); ?>" placeholder="https://twitter.com/username">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube</label>
                    <input type="url" name="youtube" value="<?php echo htmlspecialchars($about['youtube'] ?? ''); ?>" placeholder="https://youtube.com/@username">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label><i class="fab fa-instagram" style="color:#e4405f;"></i> Instagram</label>
                    <input type="url" name="instagram" value="<?php echo htmlspecialchars($about['instagram'] ?? ''); ?>" placeholder="https://instagram.com/username">
                </div>
            </div>
            <button type="submit" name="update_about" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Social Links
            </button>
        </form>
    </div>

    <!-- ===== PREVIEW ===== -->
    <div class="card" style="background:#f8f9ff;">
        <h3><i class="fas fa-eye"></i> Preview</h3>
        <p style="color:#888;margin-bottom:10px;">Click below to see how your about section looks on the website.</p>
        <a href="../index.php#about" target="_blank" class="btn btn-secondary">
            <i class="fas fa-eye"></i> View About on Portfolio
        </a>
    </div>

</div>
</body>
</html>