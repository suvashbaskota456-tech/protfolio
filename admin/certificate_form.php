<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../includes/functions.php'; require_admin();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{$file=upload_file('certificate','uploads/certificates',['jpg','jpeg','png','webp','pdf'],10485760);if(!$file)throw new RuntimeException('Please select a certificate file.');$st=$pdo->prepare("INSERT INTO certificates(title,description,file_path) VALUES(?,?,?)");$st->execute([trim($_POST['title']),trim($_POST['description']),$file]);redirect('certificates.php');}catch(Throwable $e){$error=$e->getMessage();}
}
$page_title='Upload Certificate';require 'header.php';
?>
<h1>Upload Certificate</h1><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<div class="admin-card"><form method="post" enctype="multipart/form-data" class="admin-form"><label>Certificate Title<input name="title" required></label><label>Description<textarea name="description" rows="4"></textarea></label><label>File (JPG, PNG, WEBP or PDF)<input type="file" name="certificate" required accept=".jpg,.jpeg,.png,.webp,.pdf"></label><button class="btn primary">Upload</button> <a class="btn secondary" href="certificates.php">Cancel</a></form></div>
<?php require 'footer.php'; ?>
