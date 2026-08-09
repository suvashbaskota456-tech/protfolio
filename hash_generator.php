<?php
// hash_generator.php
$password = "Admin@123";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "🔑 Password: " . $password . "<br>";
echo "🔐 Hash: " . $hash . "<br>";
echo "<hr>";
echo "📝 Copy this hash and use in database: <br>";
echo "<strong>" . $hash . "</strong>";
?>