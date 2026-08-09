<?php
// admin/manage-blog.php - Complete Blog Management System
// ================================================================

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ===== DELETE BLOG POST =====
$deleteMessage = "";
$deleteError = "";

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    if (deleteBlogPost($conn, $id)) {
        $deleteMessage = "✅ Blog post deleted successfully!";
    } else {
        $deleteError = "❌ Failed to delete blog post!";
    }
}

// ===== UPDATE STATUS =====
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    if ($status == 'published' || $status == 'draft') {
        $stmt = $conn->prepare("UPDATE blog_posts SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $deleteMessage = "✅ Blog status updated to " . ucfirst($status) . "!";
        }
        $stmt->close();
    }
}

// ===== GET FILTER =====
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ===== GET BLOG POSTS =====
if (!empty($search)) {
    $sql = "SELECT * FROM blog_posts WHERE title LIKE '%$search%' OR content LIKE '%$search%' ORDER BY created_at DESC";
    $blogPosts = $conn->query($sql);
} elseif ($filter == 'published') {
    $blogPosts = $conn->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
} elseif ($filter == 'draft') {
    $blogPosts = $conn->query("SELECT * FROM blog_posts WHERE status = 'draft' ORDER BY created_at DESC");
} else {
    $blogPosts = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
}

$totalPosts = $blogPosts ? $blogPosts->num_rows : 0;
$totalPublished = $conn->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")->fetch_assoc()['count'] ?? 0;
$totalDraft = $conn->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'draft'")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog - Admin</title>
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
        
        .container{max-width:1200px;margin:30px auto;padding:0 20px;}
        
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        
        .stats-bar{display:flex;justify-content:space-between;align-items:center;background:white;padding:15px 20px;border-radius:12px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.06);flex-wrap:wrap;gap:10px;}
        .stats-bar .total{font-size:16px;color:#333;}
        .stats-bar .total strong{color:#667eea;font-size:20px;}
        .stats-bar .total .published{color:#28a745;}
        .stats-bar .total .draft{color:#ffc107;}
        .btn-add{display:inline-block;padding:10px 24px;background:#28a745;color:white;border-radius:50px;text-decoration:none;font-weight:600;font-size:14px;transition:all 0.3s;}
        .btn-add:hover{background:#218838;transform:translateY(-2px);box-shadow:0 6px 20px rgba(40,167,69,0.3);}
        
        .filter-bar{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;}
        .filter-bar .filter-btn{padding:6px 18px;border-radius:50px;border:2px solid #e1e5e9;background:white;color:#555;font-weight:600;font-size:13px;cursor:pointer;transition:all 0.3s;text-decoration:none;}
        .filter-bar .filter-btn:hover{background:#f0f0f5;}
        .filter-bar .filter-btn.active{background:#667eea;color:white;border-color:#667eea;}
        .filter-bar .search-box{display:flex;gap:10px;margin-left:auto;}
        .filter-bar .search-box input{padding:8px 16px;border:2px solid #e1e5e9;border-radius:50px;font-size:13px;outline:none;transition:all 0.3s;}
        .filter-bar .search-box input:focus{border-color:#667eea;}
        .filter-bar .search-box button{padding:8px 20px;background:#667eea;color:white;border:none;border-radius:50px;font-weight:600;cursor:pointer;transition:all 0.3s;}
        .filter-bar .search-box button:hover{background:#5a52d5;}
        
        .blog-table{background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.06);}
        .blog-table table{width:100%;border-collapse:collapse;}
        .blog-table th,.blog-table td{padding:14px 16px;text-align:left;border-bottom:1px solid #eee;font-size:13px;}
        .blog-table th{background:#f8f9fa;font-weight:600;color:#555;}
        .blog-table tr:hover{background:#f8f9fa;}
        .blog-table .title-cell{max-width:250px;}
        .blog-table .title-cell a{color:#333;text-decoration:none;font-weight:600;}
        .blog-table .title-cell a:hover{color:#667eea;}
        .blog-table .status-badge{padding:4px 14px;border-radius:50px;font-size:11px;font-weight:600;display:inline-block;}
        .blog-table .status-badge.published{background:#d4edda;color:#155724;}
        .blog-table .status-badge.draft{background:#fff3cd;color:#856404;}
        .blog-table .actions{display:flex;gap:8px;flex-wrap:wrap;}
        .blog-table .actions .btn-edit{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#667eea;color:white;border-radius:50px;font-size:11px;font-weight:600;text-decoration:none;transition:all 0.3s;}
        .blog-table .actions .btn-edit:hover{background:#5a52d5;transform:translateY(-2px);}
        .blog-table .actions .btn-delete{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#dc3545;color:white;border-radius:50px;font-size:11px;font-weight:600;border:none;cursor:pointer;transition:all 0.3s;}
        .blog-table .actions .btn-delete:hover{background:#c82333;transform:translateY(-2px);}
        .blog-table .actions .btn-status{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#ffc107;color:#333;border-radius:50px;font-size:11px;font-weight:600;text-decoration:none;transition:all 0.3s;}
        .blog-table .actions .btn-status:hover{background:#e0a800;transform:translateY(-2px);}
        .blog-table .actions .btn-view{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#17a2b8;color:white;border-radius:50px;font-size:11px;font-weight:600;text-decoration:none;transition:all 0.3s;}
        .blog-table .actions .btn-view:hover{background:#138496;transform:translateY(-2px);}
        
        .empty-state{text-align:center;padding:60px 20px;color:#888;}
        .empty-state i{font-size:60px;display:block;margin-bottom:20px;color:#ddd;}
        .empty-state p{font-size:16px;}
        .empty-state a{color:#667eea;font-weight:600;text-decoration:none;}
        .empty-state a:hover{text-decoration:underline;}
        
        /* Modal */
        .modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;}
        .modal-overlay.active{display:flex;}
        .modal{background:white;padding:30px;border-radius:15px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
        .modal .icon{font-size:50px;display:block;margin-bottom:15px;}
        .modal h3{font-size:20px;color:#333;margin-bottom:10px;}
        .modal p{color:#666;font-size:14px;margin-bottom:20px;line-height:1.6;}
        .modal .modal-actions{display:flex;gap:12px;justify-content:center;}
        .modal .modal-actions .btn-cancel{padding:10px 28px;background:#6c757d;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.3s;}
        .modal .modal-actions .btn-cancel:hover{background:#5a6268;}
        .modal .modal-actions .btn-confirm{padding:10px 28px;background:#dc3545;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.3s;}
        .modal .modal-actions .btn-confirm:hover{background:#c82333;}
        
        @media(max-width:768px){
            .header{flex-direction:column;text-align:center;}
            .stats-bar{flex-direction:column;text-align:center;}
            .filter-bar{flex-direction:column;}
            .filter-bar .search-box{margin-left:0;width:100%;}
            .filter-bar .search-box input{flex:1;}
            .blog-table{overflow-x:auto;}
            .blog-table table{font-size:12px;}
            .blog-table th,.blog-table td{padding:10px 12px;}
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <h1><i class="fas fa-blog"></i> Manage Blog</h1>
    <div class="header-actions">
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ===== CONTAINER ===== -->
<div class="container">

    <!-- ===== ALERTS ===== -->
    <?php if(!empty($deleteMessage)): ?>
        <div class="alert alert-success"><?php echo $deleteMessage; ?></div>
    <?php endif; ?>
    <?php if(!empty($deleteError)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($deleteError); ?></div>
    <?php endif; ?>

    <!-- ===== STATS BAR ===== -->
    <div class="stats-bar">
        <div class="total">
            <i class="fas fa-blog"></i> Total: <strong><?php echo $totalPosts; ?></strong>
            | Published: <strong class="published"><?php echo $totalPublished; ?></strong>
            | Draft: <strong class="draft"><?php echo $totalDraft; ?></strong>
        </div>
        <a href="add-blog.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New Post
        </a>
    </div>

    <!-- ===== FILTER BAR ===== -->
    <div class="filter-bar">
        <a href="manage-blog.php?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">All</a>
        <a href="manage-blog.php?filter=published" class="filter-btn <?php echo $filter == 'published' ? 'active' : ''; ?>">Published</a>
        <a href="manage-blog.php?filter=draft" class="filter-btn <?php echo $filter == 'draft' ? 'active' : ''; ?>">Draft</a>
        
        <form class="search-box" method="GET">
            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
            <input type="text" name="search" placeholder="Search blog posts..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <!-- ===== BLOG TABLE ===== -->
    <?php if ($blogPosts && $blogPosts->num_rows > 0): ?>
        <div class="blog-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($post = $blogPosts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td class="title-cell">
                            <a href="../blog-detail.php?slug=<?php echo $post['slug']; ?>" target="_blank">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($post['category'] ?? '-'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $post['status']; ?>">
                                <?php echo ucfirst($post['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $post['views']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit-blog.php?id=<?php echo $post['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if ($post['status'] == 'published'): ?>
                                    <a href="manage-blog.php?status=draft&id=<?php echo $post['id']; ?>" class="btn-status">
                                        <i class="fas fa-undo"></i> Draft
                                    </a>
                                <?php else: ?>
                                    <a href="manage-blog.php?status=published&id=<?php echo $post['id']; ?>" class="btn-status">
                                        <i class="fas fa-check"></i> Publish
                                    </a>
                                <?php endif; ?>
                                <a href="../blog-detail.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo addslashes($post['title']); ?>')" class="btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-blog"></i>
            <p>No blog posts found.</p>
            <a href="add-blog.php">Create your first blog post →</a>
        </div>
    <?php endif; ?>
</div>

<!-- ===== DELETE CONFIRMATION MODAL ===== -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <span class="icon">⚠️</span>
        <h3>Delete Blog Post?</h3>
        <p>Are you sure you want to delete "<strong id="deleteItemTitle"></strong>"?<br>This action cannot be undone!</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <a href="#" class="btn-confirm" id="confirmDeleteBtn">Delete</a>
        </div>
    </div>
</div>

<!-- ===== JAVASCRIPT ===== -->
<script>
function confirmDelete(id, title) {
    document.getElementById('deleteItemTitle').textContent = title;
    document.getElementById('confirmDeleteBtn').href = 'manage-blog.php?delete=' + id;
    document.getElementById('deleteModal').classList.add('active');
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

</body>
</html>