<?php
// ================================================================
// admin/messages.php - Complete Messages with Reply Button
// ================================================================

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ===== DELETE MESSAGE =====
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $deleteMsg = "✅ Message deleted successfully!";
    }
    $stmt->close();
}

// ===== MARK AS READ =====
if (isset($_GET['read']) && !empty($_GET['read'])) {
    $id = (int)$_GET['read'];
    $conn->query("UPDATE messages SET status = 'read' WHERE id = $id");
}

// ===== MARK AS REPLIED =====
if (isset($_GET['replied']) && !empty($_GET['replied'])) {
    $id = (int)$_GET['replied'];
    $conn->query("UPDATE messages SET status = 'replied' WHERE id = $id");
}

// ===== GET ALL MESSAGES =====
$messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
$total = $messages ? $messages->num_rows : 0;

// ===== GET UNREAD COUNT =====
$unread = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'unread'")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ============================================================
           COMPLETE MESSAGES STYLES
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        /* ===== HEADER ===== */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 18px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 22px;
            font-weight: 700;
        }
        
        .header h1 i {
            margin-right: 10px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }
        
        .btn-back:hover {
            background: white;
            color: #764ba2;
            transform: translateY(-2px);
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 2px solid rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }
        
        .btn-logout:hover {
            background: #dc3545;
            border-color: #dc3545;
            transform: translateY(-2px);
        }
        
        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* ===== ALERTS ===== */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* ===== STATS BAR ===== */
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .stats-bar .total {
            font-size: 16px;
            color: #333;
        }
        
        .stats-bar .total strong {
            color: #667eea;
            font-size: 20px;
        }
        
        .stats-bar .total .unread {
            color: #dc3545;
        }
        
        .btn-refresh {
            display: inline-block;
            padding: 8px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-refresh:hover {
            background: #5a52d5;
            transform: translateY(-2px);
        }
        
        /* ===== MESSAGE TABLE ===== */
        .message-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        
        .message-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .message-table th,
        .message-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .message-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .message-table tr:hover {
            background: #f8f9fa;
        }
        
        .message-table .unread-row {
            background: #f0f7ff;
            font-weight: 600;
        }
        
        .message-table .unread-row:hover {
            background: #e8f0fe;
        }
        
        .message-table .has-reply {
            background: #f0fff4;
        }
        
        .message-table .has-reply td {
            border-bottom: 1px solid #d4edda;
        }
        
        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-unread {
            background: #dc3545;
            color: white;
        }
        
        .badge-read {
            background: #28a745;
            color: white;
        }
        
        .badge-replied {
            background: #6f42c1;
            color: white;
        }
        
        /* ===== MESSAGE PREVIEW ===== */
        .message-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-preview:hover {
            white-space: normal;
            overflow: visible;
            background: #fff;
            position: relative;
            z-index: 1;
            padding: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        
        /* ===== ACTIONS ===== */
        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .actions .btn:hover {
            transform: translateY(-2px);
        }
        
        .actions .btn-read {
            background: #28a745;
            color: white;
        }
        
        .actions .btn-read:hover {
            background: #218838;
        }
        
        .actions .btn-replied {
            background: #ffc107;
            color: #333;
        }
        
        .actions .btn-replied:hover {
            background: #e0a800;
        }
        
        /* ===== REPLY BUTTON - NEW ===== */
        .actions .btn-reply {
            background: #6f42c1;
            color: white;
        }
        
        .actions .btn-reply:hover {
            background: #5a32a3;
        }
        
        .actions .btn-email {
            background: #17a2b8;
            color: white;
        }
        
        .actions .btn-email:hover {
            background: #138496;
        }
        
        .actions .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .actions .btn-delete:hover {
            background: #c82333;
        }
        
        /* ===== REPLY INDICATOR ===== */
        .reply-indicator {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 600;
            background: #6f42c1;
            color: white;
            margin-left: 8px;
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 60px;
            display: block;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .empty-state p {
            font-size: 16px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-bar {
                flex-direction: column;
                text-align: center;
            }
            
            .message-table {
                overflow-x: auto;
            }
            
            .message-table table {
                font-size: 12px;
            }
            
            .message-table th,
            .message-table td {
                padding: 8px 10px;
            }
            
            .actions {
                flex-direction: column;
                gap: 4px;
            }
            
            .actions .btn {
                font-size: 10px;
                padding: 3px 10px;
            }
            
            .message-preview {
                max-width: 120px;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 18px;
            }
            
            .btn-back,
            .btn-logout {
                padding: 6px 14px;
                font-size: 12px;
            }
            
            .stats-bar .total {
                font-size: 14px;
            }
            
            .message-table th,
            .message-table td {
                padding: 6px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>

<!-- ============================================================
HEADER
============================================================ -->
<div class="header">
    <h1><i class="fas fa-envelope"></i> Messages</h1>
    <div class="header-actions">
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ============================================================
CONTAINER
============================================================ -->
<div class="container">

    <!-- ===== ALERTS ===== -->
    <?php if (isset($deleteMsg)): ?>
        <div class="alert alert-success"><?php echo $deleteMsg; ?></div>
    <?php endif; ?>

    <!-- ===== STATS BAR ===== -->
    <div class="stats-bar">
        <div class="total">
            <i class="fas fa-envelope"></i> Total: <strong><?php echo $total; ?></strong>
            | Unread: <strong class="unread"><?php echo $unread; ?></strong>
            | Replied: <strong style="color:#6f42c1;"><?php echo $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'replied'")->fetch_assoc()['count'] ?? 0; ?></strong>
        </div>
        <a href="?refresh=1" class="btn-refresh">
            <i class="fas fa-sync"></i> Refresh
        </a>
    </div>

    <!-- ===== MESSAGES TABLE ===== -->
    <?php if ($messages && $messages->num_rows > 0): ?>
        <div class="message-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    while ($msg = $messages->fetch_assoc()):
                        $rowClass = '';
                        if ($msg['status'] == 'unread') {
                            $rowClass = 'unread-row';
                        } elseif (!empty($msg['reply'])) {
                            $rowClass = 'has-reply';
                        }
                    ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color:#667eea;text-decoration:none;">
                                    <?php echo htmlspecialchars($msg['email']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($msg['subject'] ?? 'No Subject'); ?></td>
                            <td class="message-preview">
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <?php if (!empty($msg['reply'])): ?>
                                    <span class="reply-indicator">
                                        <i class="fas fa-reply"></i> Replied
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $msg['status']; ?>">
                                    <?php echo ucfirst($msg['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <?php if ($msg['status'] == 'unread'): ?>
                                        <a href="?read=<?php echo $msg['id']; ?>" class="btn btn-read">
                                            <i class="fas fa-check"></i> Read
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($msg['status'] != 'replied'): ?>
                                        <a href="?replied=<?php echo $msg['id']; ?>" class="btn btn-replied">
                                            <i class="fas fa-reply"></i> Replied
                                        </a>
                                    <?php endif; ?>

                                    <!-- ===== REPLY BUTTON ===== -->
                                    <a href="reply-message.php?id=<?php echo $msg['id']; ?>" class="btn btn-reply">
                                        <i class="fas fa-reply"></i> Reply
                                    </a>

                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn btn-email">
                                        <i class="fas fa-envelope"></i> Email
                                    </a>
                                    <a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this message?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>No messages yet.</p>
            <p style="font-size:14px;color:#aaa;margin-top:10px;">
                <?php if ($unread > 0): ?>
                    You have <strong><?php echo $unread; ?></strong> unread messages.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- ============================================================
    QUICK STATS
    ============================================================ -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;margin-top:20px;">
        <div style="background:white;padding:15px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.06);border-left:4px solid #dc3545;">
            <div style="font-size:24px;font-weight:700;color:#dc3545;"><?php echo $unread; ?></div>
            <div style="color:#888;font-size:12px;">Unread</div>
        </div>
        <div style="background:white;padding:15px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.06);border-left:4px solid #28a745;">
            <div style="font-size:24px;font-weight:700;color:#28a745;"><?php echo $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'read'")->fetch_assoc()['count'] ?? 0; ?></div>
            <div style="color:#888;font-size:12px;">Read</div>
        </div>
        <div style="background:white;padding:15px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.06);border-left:4px solid #6f42c1;">
            <div style="font-size:24px;font-weight:700;color:#6f42c1;"><?php echo $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'replied'")->fetch_assoc()['count'] ?? 0; ?></div>
            <div style="color:#888;font-size:12px;">Replied</div>
        </div>
        <div style="background:white;padding:15px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.06);border-left:4px solid #667eea;">
            <div style="font-size:24px;font-weight:700;color:#667eea;"><?php echo $total; ?></div>
            <div style="color:#888;font-size:12px;">Total</div>
        </div>
    </div>

</div>

</body>
</html>