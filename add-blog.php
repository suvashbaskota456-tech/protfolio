<?php
// admin/add-blog.php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    
    if (empty($title) || empty($content)) {
        $error = "Title and Content are required!";
    } else {
        $slug = createSlug($title);
        
        $check = $conn->query("SELECT id FROM blog_posts WHERE slug = '$slug'");
        if ($check->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . '/../uploads/blog/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filetype = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($filetype, $allowed)) {
                $image = time() . '_' . uniqid() . '.' . $filetype;
                $target_file = $target_dir . $image;
                move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            }
        }
        
        if (addBlogPost($conn, $title, $slug, $content, $excerpt, $image, $category, $tags, $status)) {
            $message = "✅ Blog post added successfully!";
        } else {
            $error = "❌ Failed to add blog post: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Blog Post</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
        .container{background:white;padding:40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);width:100%;max-width:700px;}
        .header{text-align:center;margin-bottom:30px;}
        .header .icon{font-size:40px;display:block;margin-bottom:10px;}
        .header h2{font-size:24px;color:#333;}
        .header p{color:#888;font-size:14px;}
        .alert{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .form-group{margin-bottom:18px;}
        .form-group label{display:block;margin-bottom:6px;color:#555;font-weight:600;font-size:14px;}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:12px 14px;border:2px solid #e1e5e9;border-radius:8px;font-size:14px;}
        .form-group input:focus,.form-group textarea:focus,.form-group select:focus{outline:none;border-color:#667eea;}
        .form-group textarea{resize:vertical;min-height:150px;}
        .form-group input[type="file"]{padding:12px;border:2px dashed #e1e5e9;cursor:pointer;background:#f8f9fa;}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
        .btn{width:100%;padding:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;}
        .btn:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(102,126,234,0.4);}
        .btn-secondary{background:#6c757d;margin-top:10px;}
        .btn-secondary:hover{background:#5a6268;}
        .btn a{color:white;text-decoration:none;display:block;}
        @media(max-width:600px){.row{grid-template-columns:1fr;}}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="icon">📝</span>
            <h2>Add Blog Post</h2>
            <p>Write a new blog post</p>
        </div>
        
        <?php if(!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" placeholder="Enter post title" required>
            </div>
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" placeholder="Write your blog content here..." required></textarea>
            </div>
            <div class="form-group">
                <label>Excerpt (Short Description)</label>
                <input type="text" name="excerpt" placeholder="Short summary of the post">
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" placeholder="e.g., Management, Audit">
                </div>
                <div class="form-group">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" placeholder="e.g., microfinance, audit">
                </div>
            </div>
            <div class="form-group">
                <label>Featured Image</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="draft">Draft</option>
                    <option value="published" selected>Published</option>
                </select>
            </div>
            <button type="submit" class="btn"><i class="fas fa-plus"></i> Add Blog Post</button>
        </form>
        <button class="btn btn-secondary"><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back</a></button>
    </div>
</body>
</html>