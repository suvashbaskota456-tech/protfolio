<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../includes/functions.php'; require_admin();
if(isset($_GET['delete'])){$st=$pdo->prepare("DELETE FROM certificates WHERE id=?");$st->execute([(int)$_GET['delete']]);flash('success','Certificate deleted.');redirect('certificates.php');}
$rows=$pdo->query("SELECT * FROM certificates ORDER BY id DESC")->fetchAll();$page_title='Certificates';require 'header.php';
?>
<h1>Certificates</h1><a class="btn primary" href="certificate_form.php">+ Upload Certificate</a>
<?php if($m=flash('success')):?><div class="alert success"><?=e($m)?></div><?php endif;?>
<div class="cert-admin-grid"><?php foreach($rows as $c):?><div class="admin-card"><img class="cert-img" src="../<?=e($c['file_path'])?>" alt=""><h3><?=e($c['title'])?></h3><p><?=e($c['description'])?></p><a class="danger-link" onclick="return confirm('Delete this certificate?')" href="?delete=<?=$c['id']?>">Delete</a></div><?php endforeach;?></div>
<?php require 'footer.php'; ?>
