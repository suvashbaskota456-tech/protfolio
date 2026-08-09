<?php
// send-message.php - Complete Contact Form Handler
// ================================================================

require_once 'config.php';

// ===== CHECK IF FORM SUBMITTED =====
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ===== GET FORM DATA =====
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // ===== VALIDATION =====
    $error = "";
    
    if (empty($name)) {
        $error = "Name is required!";
    } elseif (empty($email)) {
        $error = "Email is required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address!";
    } elseif (empty($message)) {
        $error = "Message is required!";
    }
    
    // ===== IF NO ERROR, SAVE TO DATABASE =====
    if (empty($error)) {
        
        // Check if messages table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($tableCheck->num_rows == 0) {
            // Create table if not exists
            $conn->query("CREATE TABLE IF NOT EXISTS messages (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(200) NULL,
                message TEXT NOT NULL,
                status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            // ===== SEND EMAIL NOTIFICATION (Optional) =====
            $to = ADMIN_EMAIL;
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $emailBody = "
            <html>
            <head><title>New Contact Message</title></head>
            <body>
                <h2>New Message from Portfolio</h2>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Subject:</strong> " . ($subject ?: 'No Subject') . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br($message) . "</p>
                <hr>
                <p><a href='" . SITE_URL . "admin/messages.php'>View in Admin Panel</a></p>
            </body>
            </html>
            ";
            
            @mail($to, "New Message from $name", $emailBody, $headers);
            
            // ===== SUCCESS =====
            $success = "✅ Your message has been sent successfully! I will get back to you soon.";
            
            // Store success in session for redirect
            session_start();
            $_SESSION['message_success'] = $success;
            
            header("Location: index.php#contact?success=1");
            exit();
            
        } else {
            $error = "❌ Failed to send message. Please try again!";
        }
        $stmt->close();
    }
    
    // ===== IF ERROR, REDIRECT BACK =====
    if (!empty($error)) {
        session_start();
        $_SESSION['message_error'] = $error;
        header("Location: index.php#contact?error=1");
        exit();
    }
    
} else {
    // ===== IF NOT POST, REDIRECT TO HOME =====
    header("Location: index.php");
    exit();
}
?>