<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$page_title='Admin Dashboard';
$posts=$pdo->query("SELECT COUNT(*) c FROM posts")->fetch()['c'];
$published=$pdo->query("SELECT COUNT(*) c FROM posts WHERE status='published'")->fetch()['c'];
$certs=$pdo->query("SELECT COUNT(*) c FROM certificates")->fetch()['c'];
$msgs=$pdo->query("SELECT COUNT(*) c FROM messages WHERE is_read=0")->fetch()['c'];
require 'header.php';
?>
<h1>Dashboard</h1><p class="muted">Manage your professional portfolio from one place.</p>
<div class="stats"><div><strong><?= $posts ?></strong><span>Total Posts</span></div><div><strong><?= $published ?></strong><span>Published</span></div><div><strong><?= $certs ?></strong><span>Certificates</span></div><div><strong><?= $msgs ?></strong><span>Unread Messages</span></div></div>
<div class="admin-card"><h2>Quick Actions</h2><div class="quick-actions"><a class="btn primary" href="post_form.php">+ Add Blog Post</a><a class="btn secondary" href="certificate_form.php">+ Upload Certificate</a><a class="btn secondary" href="messages.php">View Messages</a></div></div>
<?php require 'footer.php'; ?>
