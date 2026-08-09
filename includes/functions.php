<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function redirect($url) {
    header("Location: $url");
    exit;
}
function admin_logged_in() {
    return isset($_SESSION['admin_id']);
}
function require_admin() {
    if (!admin_logged_in()) {
        redirect('login.php');
    }
}
function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}
function upload_file($field, $folder, $allowed, $maxBytes = 5242880) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }
    if ($_FILES[$field]['size'] > $maxBytes) {
        throw new RuntimeException('File is too large.');
    }

    $tmp = $_FILES[$field]['tmp_name'];
    $original = $_FILES[$field]['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Invalid file type.');
    }

    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $dir = dirname(__DIR__) . '/' . trim($folder, '/') . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!move_uploaded_file($tmp, $dir . $name)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return trim($folder, '/') . '/' . $name;
}
?>