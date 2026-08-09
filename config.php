<?php
// ================================================================
// config.php - InfinityFree Final Version
// ================================================================

// ===== DATABASE SETTINGS =====
define('DB_HOST', 'sql200.infinityfree.com');
define('DB_USER', 'if0_42606716');
define('DB_PASS', 'YourStrongPassword123');
define('DB_NAME', 'if0_42606716_suvas');

// ===== CONNECTION =====
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ===== SITE SETTINGS =====
define('SITE_URL', 'https://suvashbaskota.infinityfreeapp.com/');

// ===== SESSION =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kathmandu');

// ================================================================
// FUNCTIONS
// ================================================================

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

function getCount($conn, $table) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result && $result->num_rows > 0) {
        return (int)$result->fetch_assoc()['count'];
    }
    return 0;
}

function getUnreadCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'unread'");
    if ($result && $result->num_rows > 0) {
        return (int)$result->fetch_assoc()['count'];
    }
    return 0;
}

function getRecentMessages($conn, $limit = 5) {
    $limit = (int)$limit;
    return $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT $limit");
}

function getAboutInfo($conn) {
    $result = $conn->query("SELECT * FROM about LIMIT 1");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getGalleryItems($conn, $limit = null) {
    $sql = "SELECT * FROM gallery ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $conn->query($sql);
}

function addGalleryItem($conn, $title, $description, $image, $category) {
    $stmt = $conn->prepare("INSERT INTO gallery (title, description, image, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $description, $image, $category);
    return $stmt->execute();
}

function deleteGalleryItem($conn, $id) {
    $result = $conn->query("SELECT image FROM gallery WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = __DIR__ . '/uploads/gallery/' . $row['image'];
        if (file_exists($imagePath) && !empty($row['image'])) {
            unlink($imagePath);
        }
    }
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getBlogPosts($conn, $limit = null, $status = 'published') {
    $sql = "SELECT * FROM blog_posts WHERE status = '$status' ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $conn->query($sql);
}

function addBlogPost($conn, $title, $slug, $content, $excerpt, $image, $category, $tags, $status) {
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, category, tags, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $title, $slug, $content, $excerpt, $image, $category, $tags, $status);
    return $stmt->execute();
}

function deleteBlogPost($conn, $id) {
    $result = $conn->query("SELECT featured_image FROM blog_posts WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = __DIR__ . '/uploads/blog/' . $row['featured_image'];
        if (file_exists($imagePath) && !empty($row['featured_image'])) {
            unlink($imagePath);
        }
    }
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function updateBlogViews($conn, $id) {
    $conn->query("UPDATE blog_posts SET views = views + 1 WHERE id = $id");
}

function getSkills($conn) {
    return $conn->query("SELECT * FROM skills ORDER BY percentage DESC");
}

function getFeaturedProjects($conn, $limit = 6) {
    $limit = (int)$limit;
    return $conn->query("SELECT * FROM projects WHERE featured = 1 ORDER BY created_at DESC LIMIT $limit");
}

function getServices($conn) {
    return $conn->query("SELECT * FROM services ORDER BY created_at ASC");
}

register_shutdown_function(function() use ($conn) {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
});
?>