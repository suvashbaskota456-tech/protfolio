<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../includes/functions.php'; require_admin();
$id=(int)($_GET['id']??0); $post=['title'=>'','slug'=>'','category'=>'General','content'=>'','status'=>'draft','cover_image'=>''];
if($id){$st=$pdo->prepare("SELECT * FROM posts WHERE id=?");$st->execute([$id]);$post=$st->fetch();if(!$post)redirect('posts.php');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $title=trim($_POST['title']); $slug=trim($_POST['slug']) ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/','-', $title),'-'));
    $cover=$post['cover_image']; $new=upload_file('cover_image','uploads/blog',['jpg','jpeg','png','webp']);
    if($new)$cover=$new;
    if($id){$st=$pdo->prepare("UPDATE posts SET title=?,slug=?,category=?,content=?,status=?,cover_image=? WHERE id=?");$st->execute([$title,$slug,trim($_POST['category']),trim($_POST['content']),$_POST['status'],$cover,$id]);}
    else{$st=$pdo->prepare("INSERT INTO posts(title,slug,category,content,status,cover_image,published_at) VALUES(?,?,?,?,?,?,NOW())");$st->execute([$title,$slug,trim($_POST['category']),trim($_POST['content']),$_POST['status'],$cover]);}
    redirect('posts.php');
  }catch(Throwable $e){$error=$e->getMessage();}
}
$page_title=$id?'Edit Post':'Add Post'; require 'header.php';
?>
<h1><?= $id?'Edit':'Add' ?> Blog Post</h1>
<?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<div class="admin-card"><form method="post" enctype="multipart/form-data" class="admin-form">
<label>Title<input name="title" value="<?= e($post['title']) ?>" required></label>
<label>Slug<input name="slug" value="<?= e($post['slug']) ?>" placeholder="my-first-article"></label>
<label>Category<input name="category" value="<?= e($post['category']) ?>"></label>
<label>Content<textarea name="content" rows="14" required><?= e($post['content']) ?></textarea></label>
<label>Status<select name="status"><option value="draft" <?= $post['status']=='draft'?'selected':'' ?>>Draft</option><option value="published" <?= $post['status']=='published'?'selected':'' ?>>Published</option></select></label>
<label>Cover Image<input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"></label>
<button class="btn primary" type="submit">Save Post</button> <a class="btn secondary" href="posts.php">Cancel</a>
</form></div>
<?php require 'footer.php'; ?>
