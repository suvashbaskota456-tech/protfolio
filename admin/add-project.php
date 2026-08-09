<?php
// admin/add-project.php - Add New Project
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $project_url = trim($_POST['project_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if (empty($title) || empty($category) || empty($description)) {
        $error = "Title, Category, and Description are required!";
    } else {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/projects/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        }
        
        $stmt = $conn->prepare("INSERT INTO projects (title, category, description, technologies, image, project_url, github_url, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $title, $category, $description, $technologies, $image, $project_url, $github_url, $featured);
        
        if ($stmt->execute()) {
            $message = "✅ Project added successfully!";
        } else {
            $error = "❌ Failed to add project: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Project</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f7fa;padding:20px;}
        .container{max-width:700px;margin:0 auto;background:white;padding:30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);}
        h2{color:#333;margin-bottom:20px;}
        .form-group{margin-bottom:20px;}
        label{display:block;margin-bottom:5px;color:#555;font-weight:600;}
        input,select,textarea{width:100%;padding:10px 12px;border:2px solid #e1e5e9;border-radius:8px;font-size:14px;transition:all 0.3s;}
        input:focus,select:focus,textarea:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 4px rgba(102,126,234,0.1);}
        textarea{resize:vertical;min-height:100px;}
        .checkbox-group{display:flex;align-items:center;gap:10px;}
        .checkbox-group input{width:auto;}
        .btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.3s;}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(102,126,234,0.4);}
        .btn-back{display:inline-block;padding:10px 20px;background:#6c757d;color:white;border-radius:8px;text-decoration:none;margin-top:10px;}
        .btn-back:hover{background:#5a6268;}
        .success{background:#d4edda;color:#155724;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #c3e6cb;}
        .error{background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #f5c6cb;}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
        @media(max-width:600px){.row{grid-template-columns:1fr;}}
    </style>
</head>
<body>
    <div class="container">
        <h2>📁 Add New Project</h2>
        
        <?php if(!empty($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Project Title *</label>
                <input type="text" name="title" placeholder="e.g., E-Commerce Platform" required>
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Web Development">Web Development</option>
                    <option value="UI/UX Design">UI/UX Design</option>
                    <option value="Mobile Apps">Mobile Apps</option>
                    <option value="Data Science">Data Science</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" placeholder="Describe your project..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Technologies Used</label>
                <input type="text" name="technologies" placeholder="e.g., PHP, Laravel, MySQL, React">
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label>Live URL</label>
                    <input type="url" name="project_url" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label>GitHub URL</label>
                    <input type="url" name="github_url" placeholder="https://github.com/username/project">
                </div>
            </div>
            
            <div class="form-group">
                <label>Project Image</label>
                <input type="file" name="image" accept="image/*">
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" name="featured" id="featured">
                <label for="featured">⭐ Featured Project</label>
            </div>
            
            <button type="submit" class="btn-submit">➕ Add Project</button>
        </form>
        
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</body>
</html>