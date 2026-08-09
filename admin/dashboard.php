<?php
// ================================================================
// admin/dashboard.php - Complete Admin Dashboard
// ================================================================

require_once __DIR__ . '/../config.php';

// ===== CHECK LOGIN =====
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ===== GET ADMIN INFO =====
$adminInfo = getAdminInfo($conn);

// ===== GET PROFILE PHOTO =====
$currentPhoto = 'profile.jpg';
if ($adminInfo && !empty($adminInfo['profile_image'])) {
    $currentPhoto = $adminInfo['profile_image'];
}
$photoPath = __DIR__ . '/../uploads/' . $currentPhoto;
if (!file_exists($photoPath)) {
    $currentPhoto = 'default.jpg';
}

// ===== GET STATISTICS =====
$totalProjects = getCount($conn, 'projects');
$totalSkills = getCount($conn, 'skills');
$totalServices = getCount($conn, 'services');
$totalMessages = getCount($conn, 'messages');
$unreadMessages = getUnreadCount($conn);

// ===== GALLERY & BLOG STATS =====
$totalGallery = getCount($conn, 'gallery');
$totalBlog = getCount($conn, 'blog_posts');
$totalBlogPublished = $conn->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")->fetch_assoc()['count'] ?? 0;
$totalBlogDraft = $conn->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'draft'")->fetch_assoc()['count'] ?? 0;

// ===== CHECK RESUME =====
$resumeExists = file_exists(__DIR__ . '/../uploads/resume.pdf');

// ===== GET RECENT MESSAGES =====
$recentMessages = getRecentMessages($conn, 5);

// ===== GET RECENT GALLERY ITEMS =====
$recentGallery = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 5");

// ===== GET RECENT BLOG POSTS =====
$recentBlog = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ============================================================
           COMPLETE DASHBOARD STYLES
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        /* ===== HEADER ===== */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 18px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 22px;
            font-weight: 700;
        }
        
        .header h1 i {
            margin-right: 10px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .header-actions .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.15);
            padding: 6px 16px 6px 8px;
            border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .header-actions .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .header-actions .admin-profile span {
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-view {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }
        
        .btn-view:hover {
            background: white;
            color: #764ba2;
            transform: translateY(-2px);
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 2px solid rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }
        
        .btn-logout:hover {
            background: #dc3545;
            border-color: #dc3545;
            transform: translateY(-2px);
        }
        
        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* ===== STATS GRID ===== */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-align: center;
            transition: transform 0.3s;
            border-left: 4px solid #667eea;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
        }
        
        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }
        
        .stat-card .label {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .stat-card .icon {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        /* ===== SPECIAL STATS ===== */
        .stat-card.gallery-stat {
            border-left-color: #6f42c1;
        }
        .stat-card.gallery-stat .number {
            color: #6f42c1;
        }
        
        .stat-card.blog-stat {
            border-left-color: #17a2b8;
        }
        .stat-card.blog-stat .number {
            color: #17a2b8;
        }
        
        .stat-card.resume-stat {
            border-left-color: #28a745;
        }
        .stat-card.resume-stat .number {
            color: #28a745;
        }
        
        /* ===== ACTION SECTION ===== */
        .action-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .action-card .icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .action-card h3 {
            color: #333;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .action-card p {
            color: #888;
            font-size: 12px;
            margin-bottom: 12px;
        }
        
        .btn-action {
            display: inline-block;
            padding: 6px 18px;
            background: #667eea;
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-action:hover {
            background: #5a52d5;
            transform: translateY(-2px);
        }
        
        .btn-action.about {
            background: #fd7e14;
        }
        .btn-action.about:hover {
            background: #e06b06;
        }
        
        .btn-action.resume {
            background: #28a745;
        }
        .btn-action.resume:hover {
            background: #218838;
        }
        
        .btn-action.green {
            background: #28a745;
        }
        .btn-action.green:hover {
            background: #218838;
        }
        
        .btn-action.orange {
            background: #ffc107;
            color: #333;
        }
        .btn-action.orange:hover {
            background: #e0a800;
        }
        
        .btn-action.red {
            background: #dc3545;
        }
        .btn-action.red:hover {
            background: #c82333;
        }
        
        .btn-action.purple {
            background: #6f42c1;
        }
        .btn-action.purple:hover {
            background: #5a32a3;
        }
        
        .btn-action.cyan {
            background: #17a2b8;
        }
        .btn-action.cyan:hover {
            background: #138496;
        }
        
        .btn-action.teal {
            background: #20c997;
        }
        .btn-action.teal:hover {
            background: #1ba87e;
        }
        
        /* ===== RECENT SECTION ===== */
        .recent-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        
        .section h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 17px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .section h3 i {
            margin-right: 8px;
            color: #667eea;
        }
        
        .section h3 .view-all {
            font-size: 12px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .section h3 .view-all:hover {
            text-decoration: underline;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-unread {
            background: #dc3545;
            color: white;
        }
        .badge-read {
            background: #28a745;
            color: white;
        }
        .badge-replied {
            background: #ffc107;
            color: #333;
        }
        .badge-published {
            background: #28a745;
            color: white;
        }
        .badge-draft {
            background: #ffc107;
            color: #333;
        }
        
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
            color: #ddd;
        }
        
        /* ===== MESSAGES SECTION FULL WIDTH ===== */
        .messages-section {
            grid-column: 1 / -1;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .recent-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                padding: 15px 20px;
            }
            
            .header-actions {
                justify-content: center;
            }
            
            .stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .action-section {
                grid-template-columns: 1fr 1fr;
            }
            
            .header-actions .admin-profile span {
                display: none;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 6px;
            }
        }
        
        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .action-section {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 18px;
            }
            
            .btn-view, .btn-logout {
                padding: 6px 14px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<!-- ============================================================
HEADER
============================================================ -->
<div class="header">
    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
    <div class="header-actions">
        <div class="admin-profile">
            <img src="../uploads/<?php echo $currentPhoto; ?>" alt="Profile">
            <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
        </div>
        <a href="../index.php" class="btn-view"><i class="fas fa-eye"></i> View Portfolio</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ============================================================
CONTAINER
============================================================ -->
<div class="container">

    <!-- ===== STATS ===== -->
    <div class="stats">
        <!-- Projects -->
        <div class="stat-card">
            <div class="icon">📁</div>
            <div class="number"><?php echo $totalProjects; ?></div>
            <div class="label">Projects</div>
        </div>
        
        <!-- Skills -->
        <div class="stat-card">
            <div class="icon">💪</div>
            <div class="number"><?php echo $totalSkills; ?></div>
            <div class="label">Skills</div>
        </div>
        
        <!-- Services -->
        <div class="stat-card">
            <div class="icon">🛠️</div>
            <div class="number"><?php echo $totalServices; ?></div>
            <div class="label">Services</div>
        </div>
        
        <!-- Messages -->
        <div class="stat-card">
            <div class="icon">📧</div>
            <div class="number"><?php echo $totalMessages; ?></div>
            <div class="label">Messages</div>
        </div>
        
        <!-- Unread -->
        <div class="stat-card">
            <div class="icon">🔴</div>
            <div class="number"><?php echo $unreadMessages; ?></div>
            <div class="label">Unread</div>
        </div>
        
        <!-- Gallery -->
        <div class="stat-card gallery-stat">
            <div class="icon">📸</div>
            <div class="number"><?php echo $totalGallery; ?></div>
            <div class="label">Gallery Items</div>
        </div>
        
        <!-- Blog -->
        <div class="stat-card blog-stat">
            <div class="icon">📝</div>
            <div class="number"><?php echo $totalBlog; ?></div>
            <div class="label">Blog Posts</div>
        </div>
        
        <!-- Resume -->
        <div class="stat-card resume-stat">
            <div class="icon">📄</div>
            <div class="number"><?php echo $resumeExists ? '✅' : '❌'; ?></div>
            <div class="label">Resume</div>
        </div>
    </div>

    <!-- ===== ACTION BUTTONS ===== -->
    <div class="action-section">
        <!-- ===== MANAGE ABOUT ===== -->
        <div class="action-card">
            <div class="icon">👤</div>
            <h3>Manage About</h3>
            <p>Update personal info & profile</p>
            <a href="manage-about.php" class="btn-action about"><i class="fas fa-user-edit"></i> Manage</a>
        </div>
        
        <!-- ===== MANAGE GALLERY ===== -->
        <div class="action-card">
            <div class="icon">📸</div>
            <h3>Manage Gallery</h3>
            <p>View, edit or delete gallery</p>
            <a href="manage-gallery.php" class="btn-action purple"><i class="fas fa-cog"></i> Manage</a>
        </div>
        
        <!-- ===== ADD GALLERY ===== -->
        <div class="action-card">
            <div class="icon">📸</div>
            <h3>Add Gallery</h3>
            <p>Add image with description</p>
            <a href="add-gallery.php" class="btn-action purple"><i class="fas fa-plus"></i> Add</a>
        </div>
        
        <!-- ===== MANAGE BLOG ===== -->
        <div class="action-card">
            <div class="icon">📝</div>
            <h3>Manage Blog</h3>
            <p>View, edit or delete blog posts</p>
            <a href="manage-blog.php" class="btn-action cyan"><i class="fas fa-cog"></i> Manage</a>
        </div>
        
        <!-- ===== ADD BLOG ===== -->
        <div class="action-card">
            <div class="icon">📝</div>
            <h3>Add Blog</h3>
            <p>Write new blog post</p>
            <a href="add-blog.php" class="btn-action cyan"><i class="fas fa-plus"></i> Add</a>
        </div>
        
        <!-- ===== UPLOAD RESUME ===== -->
        <div class="action-card">
            <div class="icon">📄</div>
            <h3>Upload Resume</h3>
            <p><?php echo $resumeExists ? 'Update your CV' : 'Upload your CV (PDF)'; ?></p>
            <a href="upload-resume.php" class="btn-action resume"><i class="fas fa-upload"></i> Upload</a>
        </div>
        
        <!-- ===== ADD PROJECT ===== -->
        <div class="action-card">
            <div class="icon">📁</div>
            <h3>Add Project</h3>
            <p>Add new project</p>
            <a href="add-project.php" class="btn-action"><i class="fas fa-plus"></i> Add</a>
        </div>
        
        <!-- ===== ADD SKILL ===== -->
        <div class="action-card">
            <div class="icon">💪</div>
            <h3>Add Skill</h3>
            <p>Add skill with percentage</p>
            <a href="add-skill.php" class="btn-action green"><i class="fas fa-plus"></i> Add</a>
        </div>
        
        <!-- ===== ADD SERVICE ===== -->
        <div class="action-card">
            <div class="icon">🛠️</div>
            <h3>Add Service</h3>
            <p>Add new service</p>
            <a href="add-service.php" class="btn-action orange"><i class="fas fa-plus"></i> Add</a>
        </div>
        
        <!-- ===== VIEW MESSAGES ===== -->
        <div class="action-card">
            <div class="icon">📧</div>
            <h3>Messages</h3>
            <p>Read all messages</p>
            <a href="messages.php" class="btn-action red"><i class="fas fa-envelope"></i> View</a>
        </div>
        
        <!-- ===== PROFILE PHOTO ===== -->
        <div class="action-card">
            <div class="icon">📸</div>
            <h3>Profile Photo</h3>
            <p>Update profile picture</p>
            <a href="upload-profile.php" class="btn-action teal"><i class="fas fa-upload"></i> Upload</a>
        </div>
    </div>

    <!-- ============================================================
    RECENT SECTION
    ============================================================ -->
    <div class="recent-section">
        
        <!-- ===== RECENT GALLERY ===== -->
        <div class="section">
            <h3>
                <span><i class="fas fa-images"></i> Recent Gallery</span>
                <a href="manage-gallery.php" class="view-all">View All →</a>
            </h3>
            <?php if ($recentGallery && $recentGallery->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $recentGallery->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../uploads/gallery/<?php echo $item['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                            </td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <p>No gallery items yet</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ===== RECENT BLOG ===== -->
        <div class="section">
            <h3>
                <span><i class="fas fa-blog"></i> Recent Blog Posts</span>
                <a href="manage-blog.php" class="view-all">View All →</a>
            </h3>
            <?php if ($recentBlog && $recentBlog->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($post = $recentBlog->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $post['status']; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-blog"></i>
                    <p>No blog posts yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================
    RECENT MESSAGES
    ============================================================ -->
    <div class="section messages-section">
        <h3>
            <span><i class="fas fa-clock"></i> Recent Messages</span>
            <a href="messages.php" class="view-all">View All →</a>
        </h3>
        <?php if ($recentMessages && $recentMessages->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($msg = $recentMessages->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td><?php echo htmlspecialchars($msg['subject'] ?? 'No Subject'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $msg['status']; ?>">
                                <?php echo ucfirst($msg['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No messages yet</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>