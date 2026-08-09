<?php
// admin/reply-message.php - Reply to Message
// ================================================================

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: messages.php");
    exit();
}

// Get message
$result = getMessageWithReply($conn, $id);
if ($result->num_rows == 0) {
    header("Location: messages.php");
    exit();
}

$message = $result->fetch_assoc();

// Mark as read if unread
if ($message['status'] == 'unread') {
    $conn->query("UPDATE messages SET status = 'read' WHERE id = $id");
}

$replyMessage = "";
$replyError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reply = trim($_POST['reply'] ?? '');
    
    if (empty($reply)) {
        $replyError = "Please enter a reply!";
    } else {
        // Save reply to database
        if (saveReply($conn, $id, $reply)) {
            // Send email
            $subject = "Re: " . ($message['subject'] ?? 'Your Message');
            sendReplyEmail($message['email'], $subject, $reply, $message['message']);
            
            $replyMessage = "✅ Reply sent successfully!";
            
            // Refresh message data
            $result = getMessageWithReply($conn, $id);
            $message = $result->fetch_assoc();
        } else {
            $replyError = "❌ Failed to send reply!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f2f5;}
        
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:18px 30px;color:white;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;}
        .header h1{font-size:22px;font-weight:700;}
        .header h1 i{margin-right:10px;}
        .header-actions{display:flex;align-items:center;gap:15px;flex-wrap:wrap;}
        .btn-back{background:rgba(255,255,255,0.2);color:white;border:2px solid rgba(255,255,255,0.3);padding:8px 20px;border-radius:50px;text-decoration:none;transition:all 0.3s;font-weight:500;font-size:14px;}
        .btn-back:hover{background:white;color:#764ba2;transform:translateY(-2px);}
        .btn-logout{background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.2);padding:8px 20px;border-radius:50px;text-decoration:none;transition:all 0.3s;font-weight:500;font-size:14px;}
        .btn-logout:hover{background:#dc3545;border-color:#dc3545;transform:translateY(-2px);}
        
        .container{max-width:800px;margin:30px auto;padding:0 20px;}
        
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        
        .card{background:white;border-radius:12px;padding:25px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.06);}
        .card h3{font-size:17px;font-weight:700;margin-bottom:15px;color:#333;padding-bottom:10px;border-bottom:2px solid #f0f0f5;}
        .card h3 i{margin-right:8px;color:#667eea;}
        
        .message-detail .info-row{display:flex;padding:8px 0;border-bottom:1px solid #f5f5f5;}
        .message-detail .info-row .label{font-weight:600;color:#555;width:100px;flex-shrink:0;}
        .message-detail .info-row .value{color:#333;word-break:break-word;}
        .message-detail .message-box{background:#f8f9fa;padding:15px;border-radius:8px;margin-top:10px;border-left:4px solid #667eea;}
        .message-detail .message-box p{margin:5px 0;color:#333;line-height:1.8;}
        
        .reply-box .reply-old{background:#f0f7ff;padding:15px;border-radius:8px;margin-bottom:15px;border-left:4px solid #28a745;}
        .reply-box .reply-old p{margin:5px 0;color:#333;line-height:1.8;}
        .reply-box .reply-old .reply-label{font-weight:600;color:#28a745;font-size:13px;}
        
        .form-group{margin-bottom:18px;}
        .form-group label{display:block;margin-bottom:6px;color:#555;font-weight:600;font-size:14px;}
        .form-group textarea{width:100%;padding:12px 14px;border:2px solid #e1e5e9;border-radius:8px;font-size:14px;min-height:150px;resize:vertical;transition:all 0.3s;}
        .form-group textarea:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 4px rgba(102,126,234,0.1);}
        
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;border:none;transition:all 0.3s;text-decoration:none;}
        .btn-primary{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(102,126,234,0.3);}
        .btn-secondary{background:#6c757d;color:white;}
        .btn-secondary:hover{background:#5a6268;transform:translateY(-2px);}
        .btn-success{background:#28a745;color:white;}
        .btn-success:hover{background:#218838;transform:translateY(-2px);}
        
        .btn-group{display:flex;gap:12px;flex-wrap:wrap;}
        
        @media(max-width:768px){
            .header{flex-direction:column;text-align:center;}
            .message-detail .info-row{flex-direction:column;}
            .message-detail .info-row .label{width:100%;}
            .btn-group{flex-direction:column;}
            .btn-group .btn{width:100%;justify-content:center;}
        }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-reply"></i> Reply to Message</h1>
    <div class="header-actions">
        <a href="messages.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="container">
    
    <?php if(!empty($replyMessage)): ?>
        <div class="alert alert-success"><?php echo $replyMessage; ?></div>
    <?php endif; ?>
    <?php if(!empty($replyError)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($replyError); ?></div>
    <?php endif; ?>
    
    <!-- ===== MESSAGE DETAIL ===== -->
    <div class="card message-detail">
        <h3><i class="fas fa-envelope"></i> Message Details</h3>
        <div class="info-row">
            <span class="label">From:</span>
            <span class="value"><strong><?php echo htmlspecialchars($message['name']); ?></strong> (<?php echo htmlspecialchars($message['email']); ?>)</span>
        </div>
        <div class="info-row">
            <span class="label">Subject:</span>
            <span class="value"><?php echo htmlspecialchars($message['subject'] ?? 'No Subject'); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Status:</span>
            <span class="value">
                <span class="badge badge-<?php echo $message['status']; ?>">
                    <?php echo ucfirst($message['status']); ?>
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="label">Date:</span>
            <span class="value"><?php echo date('F j, Y h:i A', strtotime($message['created_at'])); ?></span>
        </div>
        <div class="message-box">
            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
        </div>
    </div>
    
    <!-- ===== PREVIOUS REPLY ===== -->
    <?php if (!empty($message['reply'])): ?>
    <div class="card reply-box">
        <h3><i class="fas fa-reply-all"></i> Previous Reply</h3>
        <div class="reply-old">
            <span class="reply-label"><i class="fas fa-reply"></i> My Reply</span>
            <p><?php echo nl2br(htmlspecialchars($message['reply'])); ?></p>
            <?php if ($message['reply_date']): ?>
                <p style="font-size:12px;color:#888;margin-top:5px;">
                    Sent: <?php echo date('F j, Y h:i A', strtotime($message['reply_date'])); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ===== REPLY FORM ===== -->
    <?php if ($message['status'] != 'replied' || !empty($message['reply'])): ?>
    <div class="card">
        <h3><i class="fas fa-edit"></i> <?php echo !empty($message['reply']) ? 'Edit Reply' : 'Write Reply'; ?></h3>
        <form method="POST">
            <div class="form-group">
                <label>Your Reply *</label>
                <textarea name="reply" placeholder="Write your reply here..." required><?php echo htmlspecialchars($message['reply'] ?? ''); ?></textarea>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Send Reply
                </button>
                <a href="messages.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- ===== NOTE ===== -->
    <div class="card" style="background:#f8f9ff;border:1px solid #e8ecff;">
        <p style="color:#666;font-size:13px;">
            <i class="fas fa-info-circle" style="color:#667eea;"></i>
            The reply will be sent to <strong><?php echo htmlspecialchars($message['email']); ?></strong> via email and saved in the database.
        </p>
    </div>
</div>

</body>
</html>