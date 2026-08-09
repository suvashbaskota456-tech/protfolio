<?php
// admin/manage-gallery.php - Complete Gallery Management with Delete
// ================================================================

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ===== DELETE GALLERY ITEM =====
$deleteMessage = "";
$deleteError = "";

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    if (deleteGalleryItem($conn, $id)) {
        $deleteMessage = "✅ Gallery item deleted successfully!";
    } else {
        $deleteError = "❌ Failed to delete gallery item!";
    }
}

// ===== GET ALL GALLERY ITEMS =====
$galleryItems = getGalleryItems($conn);
$totalItems = $galleryItems ? $galleryItems->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Admin</title>
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
        .btn-add{display:inline-block;padding:10px 24px;background:#28a745;color:white;border-radius:50px;text-decoration:none;font-weight:600;font-size:14px;transition:all 0.3s;}
        .btn-add:hover{background:#218838;transform:translateY(-2px);box-shadow:0 6px 20px rgba(40,167,69,0.3);}
        
        .gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:25px;}
        
        .gallery-card{background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.06);transition:all 0.3s;}
        .gallery-card:hover{transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,0.1);}
        
        .gallery-card .image{height:200px;overflow:hidden;position:relative;}
        .gallery-card .image img{width:100%;height:100%;object-fit:cover;transition:transform 0.3s;}
        .gallery-card:hover .image img{transform:scale(1.05);}
        
        .gallery-card .image .category-badge{position:absolute;top:12px;left:12px;background:#667eea;color:white;padding:4px 14px;border-radius:50px;font-size:11px;font-weight:600;}
        
        .gallery-card .info{padding:16px 18px;}
        .gallery-card .info h3{font-size:16px;font-weight:700;margin-bottom:5px;color:#333;}
        .gallery-card .info p{color:#888;font-size:13px;line-height:1.6;margin-bottom:12px;}
        .gallery-card .info .meta{font-size:12px;color:#aaa;margin-bottom:12px;display:block;}
        
        .gallery-card .actions{display:flex;gap:10px;border-top:1px solid #eee;padding:12px 18px;background:#f8f9fa;}
        .btn-delete{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;background:#dc3545;color:white;border:none;border-radius:50px;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;}
        .btn-delete:hover{background:#c82333;transform:translateY(-2px);}
        .btn-view{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;background:#667eea;color:white;border:none;border-radius:50px;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;}
        .btn-view:hover{background:#5a52d5;transform:translateY(-2px);}
        
        .empty-state{text-align:center;padding:60px 20px;color:#888;}
        .empty-state i{font-size:60px;display:block;margin-bottom:20px;color:#ddd;}
        .empty-state p{font-size:16px;}
        .empty-state a{color:#667eea;font-weight:600;text-decoration:none;}
        .empty-state a:hover{text-decoration:underline;}
        
        /* Delete Confirmation Modal */
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
            .gallery-grid{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<div class="header">
    <h1><i class="fas fa-images"></i> Manage Gallery</h1>
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
            <i class="fas fa-images"></i> Total Items: <strong><?php echo $totalItems; ?></strong>
        </div>
        <a href="add-gallery.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New
        </a>
    </div>

    <!-- ===== GALLERY GRID ===== -->
    <?php if ($galleryItems && $galleryItems->num_rows > 0): ?>
        <div class="gallery-grid">
            <?php while($item = $galleryItems->fetch_assoc()): ?>
            <div class="gallery-card">
                <div class="image">
                    <img src="../uploads/gallery/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php if($item['category']): ?>
                        <span class="category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <span class="meta">
                        <i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                    </span>
                </div>
                <div class="actions">
                    <a href="../gallery.php" target="_blank" class="btn-view">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <button onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo addslashes($item['title']); ?>')" class="btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-images"></i>
            <p>No gallery items added yet.</p>
            <a href="add-gallery.php">Add your first gallery item →</a>
        </div>
    <?php endif; ?>
</div>

<!-- ===== DELETE CONFIRMATION MODAL ===== -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <span class="icon">⚠️</span>
        <h3>Delete Gallery Item?</h3>
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
    document.getElementById('confirmDeleteBtn').href = 'manage-gallery.php?delete=' + id;
    document.getElementById('deleteModal').classList.add('active');
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

</body>
</html>