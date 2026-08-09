<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php#contact');

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$subject || !$message) {
    flash('error', 'Please fill all fields correctly.');
    redirect('index.php#contact');
}
$st = $pdo->prepare("INSERT INTO messages (name,email,subject,message) VALUES (?,?,?,?)");
$st->execute([$name,$email,$subject,$message]);
flash('success', 'Your message has been sent successfully.');
redirect('index.php#contact');
?>