<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../includes/functions.php'; require_admin();
if (isset($_GET['delete'])) { $st=$pdo->prepare("DELETE FROM posts WHERE id=?"); $st->execute([(int)$_GET['delete']]); flash('success','Post deleted.'); redirect('posts.php'); }
$posts=$pdo->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll(); $page_title='Blog Posts'; require 'header.php';
?>
<h1>Blog Posts</h1><a class="btn primary" href="post_form.php">+ Add New Post</a>
<?php if($m=flash('success')): ?><div class="alert success"><?= e($m) ?></div><?php endif; ?>
<div class="admin-card table-wrap"><table><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php foreach($posts as $p): ?><tr><td><?= e($p['title']) ?></td><td><?= e($p['category']) ?></td><td><?= e($p['status']) ?></td><td><?= e($p['published_at']) ?></td><td><a href="post_form.php?id=<?= $p['id'] ?>">Edit</a> · <a class="danger-link" onclick="return confirm('Delete this post?')" href="?delete=<?= $p['id'] ?>">Delete</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require 'footer.php'; ?>
