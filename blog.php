<?php
// blog.php
require_once 'config.php';

$blogPosts = getBlogPosts($conn, null, 'published');
$isLoggedIn = isAdminLoggedIn();
$resumePath = __DIR__ . '/uploads/resume.pdf';
$resumeExists = file_exists($resumePath);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Blog - Suvash Baskota</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial;background:#f5f5f5;}
        .container{max-width:1200px;margin:0 auto;padding:20px;}
        .header{background:#0a0a1a;padding:15px 0;color:white;}
        .header .container{display:flex;justify-content:space-between;align-items:center;}
        .header a{color:white;text-decoration:none;margin:0 10px;}
        .header .logo{font-size:24px;font-weight:800;}
        .header .logo span{color:#6C63FF;}
        .page-header{padding:100px 0 40px;text-align:center;background:#0a0a1a;color:white;}
        .page-header h1{font-size:40px;}
        .page-header h1 span{color:#6C63FF;}
        .blog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:25px;padding:40px 0;}
        .blog-card{background:white;border-radius:10px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
        .blog-card img{width:100%;height:200px;object-fit:cover;}
        .blog-content{padding:20px;}
        .blog-content .meta{color:#888;font-size:13px;margin-bottom:8px;}
        .blog-content h3{font-size:20px;margin-bottom:10px;}
        .blog-content h3 a{color:#333;text-decoration:none;}
        .blog-content h3 a:hover{color:#6C63FF;}
        .blog-content p{color:#666;font-size:14px;line-height:1.7;}
        .read-more{color:#6C63FF;font-weight:600;text-decoration:none;}
        .read-more:hover{text-decoration:underline;}
        .blog-category{display:inline-block;background:#6C63FF;color:white;padding:3px 12px;border-radius:50px;font-size:12px;position:absolute;top:15px;left:15px;}
        .blog-image{position:relative;}
        .empty-state{text-align:center;padding:60px;color:#888;}
        .empty-state i{font-size:50px;display:block;margin-bottom:15px;}
        .footer{background:#0a0a1a;color:white;padding:30px 0;text-align:center;}
        @media(max-width:768px){.header .container{flex-direction:column;gap:10px;}}
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo"><a href="index.php">SB<span>.</span></a></div>
            <nav>
                <a href="index.php">Home</a>
                <a href="gallery.php">Gallery</a>
                <a href="blog.php">Blog</a>
                <a href="index.php#contact">Contact</a>
            </nav>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <h1>📝 My <span>Blog</span></h1>
            <p>Insights, tutorials and professional thoughts</p>
        </div>
    </section>

    <section class="container">
        <?php if ($blogPosts && $blogPosts->num_rows > 0): ?>
            <div class="blog-grid">
                <?php while($post = $blogPosts->fetch_assoc()): ?>
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="uploads/blog/<?php echo $post['featured_image'] ?? 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <?php if($post['category']): ?>
                            <span class="blog-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="blog-content">
                        <div class="meta">
                            <i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                            <span style="margin-left:15px;"><i class="far fa-eye"></i> <?php echo $post['views']; ?></span>
                        </div>
                        <h3><a href="blog-detail.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <a href="blog-detail.php?slug=<?php echo $post['slug']; ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-blog"></i>
                <p>No blog posts published yet.</p>
                <?php if($isLoggedIn): ?>
                    <a href="admin/add-blog.php" style="color:#6C63FF;">Add Blog Post →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Suvash Baskota. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>