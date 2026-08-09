<?php
// blog-detail.php - Blog Detail Page
require_once 'config.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: blog.php");
    exit();
}

$result = getBlogPostBySlug($conn, $slug);

if ($result->num_rows == 0) {
    header("Location: blog.php");
    exit();
}

$post = $result->fetch_assoc();
updateBlogViews($conn, $post['id']);

$isLoggedIn = isAdminLoggedIn();
$resumePath = __DIR__ . '/uploads/resume.pdf';
$resumeExists = file_exists($resumePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Suvash Baskota</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{--primary:#6C63FF;--gradient:linear-gradient(135deg,#6C63FF 0%,#FF6584 100%);--dark:#1a1a2e;--gray:#8c8c9e;--white:#ffffff;--shadow:0 10px 40px rgba(0,0,0,0.08);--radius:12px;--transition:all 0.3s ease;--font:'Inter',sans-serif;}
        body{font-family:var(--font);background:#f8f9ff;color:var(--dark);}
        .container{max-width:800px;margin:0 auto;padding:0 24px;}
        
        .header{position:fixed;top:0;left:0;width:100%;z-index:1000;background:#0a0a1a;border-bottom:1px solid rgba(255,255,255,0.05);}
        .navbar{padding:16px 0;}
        .nav-content{display:flex;justify-content:space-between;align-items:center;}
        .logo a{font-size:24px;font-weight:800;color:var(--white);text-decoration:none;letter-spacing:2px;}
        .logo a span{color:var(--primary);}
        .nav-menu{display:flex;list-style:none;gap:30px;}
        .nav-menu a{color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;font-weight:500;transition:var(--transition);text-transform:uppercase;letter-spacing:1px;}
        .nav-menu a:hover,.nav-menu a.active{color:var(--white);}
        .nav-actions{display:flex;align-items:center;gap:15px;}
        .btn-nav-outline{padding:8px 20px;background:transparent;color:var(--white);border:2px solid rgba(255,255,255,0.2);border-radius:50px;font-weight:600;font-size:13px;text-decoration:none;transition:var(--transition);}
        .btn-nav-outline:hover{border-color:var(--primary);background:var(--primary);transform:translateY(-2px);}
        .btn-resume{padding:8px 24px;background:#28a745;color:var(--white);border-radius:50px;font-weight:600;font-size:13px;text-decoration:none;transition:var(--transition);}
        .btn-resume:hover{background:#218838;transform:translateY(-2px);}
        .btn-resume-missing{padding:8px 24px;background:#dc3545;color:var(--white);border-radius:50px;font-weight:600;font-size:13px;text-decoration:none;transition:var(--transition);}
        .btn-resume-missing:hover{background:#c82333;transform:translateY(-2px);}
        .hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:5px;}
        .hamburger span{width:25px;height:2px;background:var(--white);transition:var(--transition);border-radius:50px;}
        .hamburger.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px);}
        .hamburger.active span:nth-child(2){opacity:0;}
        .hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px);}
        
        .blog-detail{padding:130px 0 60px;}
        .blog-detail .meta{color:var(--gray);font-size:14px;margin-bottom:15px;}
        .blog-detail .meta i{margin-right:6px;}
        .blog-detail h1{font-size:36px;font-weight:800;margin-bottom:15px;line-height:1.2;}
        .blog-detail .featured-image{width:100%;height:400px;object-fit:cover;border-radius:var(--radius);margin:20px 0 30px;}
        .blog-detail .content{font-size:17px;line-height:1.9;color:#333;}
        .blog-detail .content p{margin-bottom:20px;}
        .blog-detail .tags{margin-top:30px;padding-top:20px;border-top:1px solid #eee;}
        .blog-detail .tags span{display:inline-block;background:#f0f0f5;padding:4px 14px;border-radius:50px;font-size:13px;margin:4px;color:#555;}
        .btn-back{display:inline-block;margin-top:30px;padding:10px 28px;background:var(--primary);color:white;border-radius:50px;text-decoration:none;transition:var(--transition);font-weight:600;}
        .btn-back:hover{background:var(--primary-dark);transform:translateY(-2px);}
        
        .footer{background:#0a0a1a;color:var(--white);padding:40px 0 20px;margin-top:40px;}
        .footer-content{text-align:center;}
        .footer-logo h3{font-size:28px;font-weight:800;margin-bottom:8px;}
        .footer-logo h3 span{color:var(--primary);}
        .footer-logo p{color:rgba(255,255,255,0.4);}
        .footer-social{display:flex;justify-content:center;gap:16px;margin:20px 0;}
        .footer-social a{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:center;transition:var(--transition);color:rgba(255,255,255,0.6);font-size:18px;border:1px solid rgba(255,255,255,0.05);}
        .footer-social a:hover{background:var(--gradient);color:var(--white);transform:translateY(-4px);}
        .footer-bottom{border-top:1px solid rgba(255,255,255,0.05);padding-top:20px;margin-top:20px;}
        .footer-bottom p{font-size:14px;color:rgba(255,255,255,0.3);}
        
        @media(max-width:768px){
            .nav-menu{display:none;position:absolute;top:100%;left:0;width:100%;background:#0a0a1a;flex-direction:column;padding:20px;gap:16px;border-top:1px solid rgba(255,255,255,0.05);}
            .nav-menu.active{display:flex;}
            .hamburger{display:flex;}
            .blog-detail h1{font-size:28px;}
            .blog-detail .featured-image{height:250px;}
        }
    </style>
</head>
<body>

<header class="header" id="header">
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="logo"><a href="index.php">SB<span>.</span></a></div>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#portfolio">Portfolio</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="blog.php" class="active">Blog</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
                <div class="nav-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="admin/dashboard.php" class="btn-nav-outline" style="border-color:rgba(108,99,255,0.5);"><i class="fas fa-user-shield"></i> Dashboard</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn-nav-outline"><i class="fas fa-lock"></i> Admin</a>
                    <?php endif; ?>
                    <?php if ($resumeExists): ?>
                        <a href="uploads/resume.pdf" class="btn-resume" download><i class="fas fa-download"></i> Resume</a>
                    <?php else: ?>
                        <a href="index.php#contact" class="btn-resume-missing"><i class="fas fa-exclamation-circle"></i> Resume</a>
                    <?php endif; ?>
                    <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
                </div>
            </div>
        </div>
    </nav>
</header>

<section class="blog-detail">
    <div class="container">
        <div class="meta">
            <i class="far fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            <span style="margin-left:20px;"><i class="far fa-eye"></i> <?php echo $post['views']; ?> views</span>
            <?php if ($post['category']): ?>
                <span style="margin-left:20px;"><i class="far fa-folder"></i> <?php echo htmlspecialchars($post['category']); ?></span>
            <?php endif; ?>
        </div>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if ($post['featured_image']): ?>
            <img src="uploads/blog/<?php echo $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="featured-image">
        <?php endif; ?>
        <div class="content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        <?php if ($post['tags']): ?>
            <div class="tags">
                <?php 
                $tags = explode(',', $post['tags']);
                foreach($tags as $tag): 
                ?>
                    <span>#<?php echo trim(htmlspecialchars($tag)); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a href="blog.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Blog</a>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo"><h3>SB<span>.</span></h3><p>Microfinance Professional & Auditor</p></div>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
            <div class="footer-bottom"><p>&copy; <?php echo date('Y'); ?> Suvash Baskota. All rights reserved.</p></div>
        </div>
    </div>
</footer>

<script>
document.getElementById('hamburger').addEventListener('click', function(){
    this.classList.toggle('active');
    document.getElementById('navMenu').classList.toggle('active');
});
</script>

</body>
</html>