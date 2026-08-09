<?php
// setup.php - Complete Setup Script
// ============================================================

echo "<h1>🔧 Portfolio Setup Tool</h1>";
echo "<hr>";

// ===== 1. CREATE FOLDERS =====
echo "<h3>1. Creating Folders...</h3>";

$folders = [
    'uploads',
    'uploads/gallery',
    'uploads/blog',
    'uploads/projects'
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0777, true)) {
            echo "✅ Created: <strong>$folder</strong><br>";
        } else {
            echo "❌ Failed: <strong>$folder</strong><br>";
        }
    } else {
        echo "✅ Already exists: <strong>$folder</strong><br>";
    }
}

// ===== 2. CHECK PERMISSIONS =====
echo "<hr>";
echo "<h3>2. Checking Permissions...</h3>";

foreach ($folders as $folder) {
    if (is_dir($folder)) {
        $writable = is_writable($folder) ? '✅ Writable' : '❌ Not Writable';
        echo "📁 $folder - $writable<br>";
    }
}

// ===== 3. CREATE INDEX.HTML (Security) =====
echo "<hr>";
echo "<h3>3. Creating Security Files...</h3>";

$folders_with_index = ['uploads', 'uploads/gallery', 'uploads/blog', 'uploads/projects'];
$index_content = "<?php header('Location: ../index.php'); exit(); ?>";

foreach ($folders_with_index as $folder) {
    $index_file = $folder . '/index.php';
    if (!file_exists($index_file)) {
        file_put_contents($index_file, $index_content);
        echo "✅ Created: <strong>$index_file</strong><br>";
    }
}

// ===== 4. DEFAULT IMAGES =====
echo "<hr>";
echo "<h3>4. Creating Default Images...</h3>";

// Default profile image (if not exists)
if (!file_exists('uploads/default.jpg')) {
    // Create a simple default image
    $img = imagecreate(400, 400);
    $bg = imagecolorallocate($img, 200, 200, 220);
    $text_color = imagecolorallocate($img, 100, 100, 150);
    imagestring($img, 5, 150, 190, 'Profile', $text_color);
    imagejpeg($img, 'uploads/default.jpg');
    imagedestroy($img);
    echo "✅ Created: <strong>uploads/default.jpg</strong><br>";
}

// ===== 5. COMPLETE =====
echo "<hr>";
echo "<h2 style='color:green;'>✅ Setup Complete!</h2>";
echo "<p>All folders are ready!</p>";
echo "<p><a href='admin/dashboard.php' style='display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;'>Go to Dashboard →</a></p>";
echo "<p><a href='index.php' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>View Portfolio →</a></p>";

echo "<hr>";
echo "<p style='color:#888;font-size:12px;'><strong>⚠️ Security Tip:</strong> Delete this file after setup!</p>";
echo "<p><a href='?delete=1' style='color:#dc3545;font-weight:bold;' onclick='return confirm(\"Delete this file?\")'>Delete setup.php</a></p>";

// ===== DELETE SELF =====
if (isset($_GET['delete']) && $_GET['delete'] == 1) {
    if (unlink(__FILE__)) {
        echo "<script>alert('✅ setup.php deleted successfully!'); window.location.href='index.php';</script>";
    }
}
?>