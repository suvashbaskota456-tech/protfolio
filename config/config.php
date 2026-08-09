<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'suvash_portfolio');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

define('SITE_NAME', 'Suvash Baskota - Portfolio');
define('SITE_URL', 'http://localhost/suvash-portfolio/');
define('ADMIN_EMAIL', 'suvashbaskota456@gmail.com');

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

date_default_timezone_set('Asia/Kathmandu');

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function getAdminInfo($conn) {
    if (!isAdminLoggedIn()) return null;
    
    $stmt = $conn->prepare("SELECT id, name, username, email, profile_image FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}
?>