<?php
// gallery.php - Complete Gallery with Admin Delete Option
require_once 'config.php';

$galleryItems = getGalleryItems($conn);
$isLoggedIn = isAdminLoggedIn();

// ===== HANDLE DELETE (Only for Admin) =====
if ($isLoggedIn && isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteGalleryItem($conn, $id)) {
        header("Location: gallery.php?deleted=1");
        exit();
    }
}

$deleted = isset($_GET['deleted']) ? true : false;

$resumePath = __DIR__ . '/uploads/resume.pdf';
$resumeExists = file_exists($resumePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Suvash Baskota</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{--primary:#6C63FF;--gradient:linear-gradient(135deg,#6C63FF 0%,#FF6584 100%);--dark:#1a1a2e;--gray:#8c8c9e;--white:#ffffff;--shadow:0 10px 40px rgba(0,0,0,0.08);--radius:12px;--transition:all 0.3s ease;--font:'Inter',sans-serif;}
        body{font-family:var(--font);background:#f8f9ff;color:var(--dark);}
        .container{max-width:1200px;margin:0 auto;padding:0 24px;}
        
        .header{position:fixed;top:0;left:0;width:100%;z-index:1000;background:#0a0a1a;border-bottom:1px solid rgba(255,255,255,0.05);}
        .navbar{padding:16px 0;}
        .nav-content{display:flex;justify-content:space-between;align-items:center;}
        .logo a{font-size:24px;font-weight:800;color:var(--white);text-decoration:none;letter-spacing:2px;}
        .logo a span{color:var(--primary);}
        .nav-menu{display:flex;list-style:none;gap:30px;}
        .nav-menu a{color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;font-weight:500;transition:var(--transition);text-transform:uppercase;letter-spacing:1px;}
        .nav-menu a:hover,.nav-menu a.active{color:var(--white);}
        .nav-menu a.active{color:var(--primary);}
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
        
        .page-header{padding:120px 0 60px;background:linear-gradient(135deg,#0a0a1a 0%,#1a1a2e 100%);text-align:center;}
        .page-header h1{font-size:48px;font-weight:900;color:var(--white);}
        .page-header h1 span{background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .page-header p{color:rgba(255,255,255,0.6);font-size:18px;margin-top:10px;}
        
        .gallery-section{padding:60px 0;}
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:30px;}
        .gallery-item{background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:var(--transition);}
        .gallery-item:hover{transform:translateY(-8px);box-shadow:0 20px 60px rgba(0,0,0,0.12);}
        .gallery-image{height:250px;overflow:hidden;position:relative;}
        .gallery-image img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease;}
        .gallery-item:hover .gallery-image img{transform:scale(1.05);}
        .gallery-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(10,10,26,0.6);display:flex;align-items:center;justify-content:center;opacity:0;transition:var(--transition);}
        .gallery-item:hover .gallery-overlay{opacity:1;}
        .gallery-overlay i{font-size:40px;color:var(--white);}
        .gallery-info{padding:20px 24px;}
        .gallery-info h3{font-size:18px;font-weight:700;margin-bottom:6px;}
        .gallery-info p{color:var(--gray);font-size:14px;line-height:1.6;}
        .gallery-info .category{display:inline-block;background:rgba(108,99,255,0.1);color:var(--primary);padding:4px 12px;border-radius:50px;font-size:12px;font-weight:600;margin-top:8px;}
        
        .btn-delete{display:inline-block;padding:4px 14px;background:#dc3545;color:white;border-radius:50px;font-size:12px;text-decoration:none;transition:all 0.3s;border:none;cursor:pointer;}
        .btn-delete:hover{background:#c82333;transform:translateY(-2px);}
        
        .empty-state{text-align:center;padding:80px 20px;color:var(--gray);}
        .empty-state i{font-size:60px;display:block;margin-bottom:20px;color:#ddd;}
        
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
            .page-header h1{font-size:32px;}
            .gallery-grid{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
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
                    <li><a href="gallery.php" class="active">Gallery</a></li>
                    <li><a href="blog.php">Blog</a></li>
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

<!-- ===== PAGE HEADER ===== -->
<section class="page-header">
    <div class="container">
        <h1>📸 My <span>Gallery</span></h1>
        <p>Explore my work and projects</p>
    </div>
</section>

<!-- ===== GALLERY SECTION ===== -->
<section class="gallery-section">
    <div class="container">
        
        <?php if($deleted): ?>
            <div class="alert alert-success">✅ Gallery item deleted successfully!</div>
        <?php endif; ?>
        
        <?php if ($galleryItems && $galleryItems->num_rows > 0): ?>
            <div class="gallery-grid">
                <?php while($item = $galleryItems->fetch_assoc()): ?>
                <div class="gallery-item">
                    <div class="gallery-image">
                        <img src="uploads/gallery/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="gallery-overlay">
                            <i class="fas fa-expand"></i>
                        </div>
                    </div>
                    <div class="gallery-info">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if ($item['category']): ?>
                            <span class="category"><?php echo htmlspecialchars($item['category']); ?></span>
                        <?php endif; ?>
                        
                        <!-- ===== DELETE OPTION (Only for Admin) ===== -->
                        <?php if ($isLoggedIn): ?>
                            <div style="margin-top:12px;padding-top:12px;border-top:1px solid #eee;">
                                <a href="gallery.php?delete=<?php echo $item['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this gallery item?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <p>No gallery items added yet.</p>
                <?php if ($isLoggedIn): ?>
                    <a href="admin/add-gallery.php" style="color:#667eea;font-weight:600;">Add Gallery Item →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== FOOTER ===== -->
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