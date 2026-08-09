<?php
// create-folders.php - Folders Create गर्ने Script
// ============================================================

echo "<h1>📁 Folder Creation Tool</h1>";

// ===== FOLDERS LIST =====
$folders = [
    'uploads/gallery',
    'uploads/blog',
    'uploads/projects'
];

// ===== CREATE FOLDERS =====
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0777, true)) {
            echo "✅ Folder created: <strong>$folder</strong><br>";
        } else {
            echo "❌ Failed to create: <strong>$folder</strong><br>";
        }
    } else {
        echo "✅ Folder already exists: <strong>$folder</strong><br>";
    }
}

// ===== CHECK PERMISSIONS =====
echo "<hr>";
echo "<h3>📋 Folder Status:</h3>";

$folders = ['uploads', 'uploads/gallery', 'uploads/blog', 'uploads/projects'];
foreach ($folders as $folder) {
    if (is_dir($folder)) {
        $writable = is_writable($folder) ? 'Writable ✅' : 'Not Writable ❌';
        echo "📁 $folder - $writable<br>";
    } else {
        echo "❌ $folder - Not Found<br>";
    }
}

echo "<hr>";
echo "<p>✅ All folders created successfully!</p>";
echo "<p><a href='admin/dashboard.php'>Go to Dashboard →</a></p>";
?>