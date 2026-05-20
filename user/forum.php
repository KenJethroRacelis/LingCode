<?php
session_start();

// 1. Authorization Guard: Stop logged-out traffic from spying on the feed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// 2. Establish database connection
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 3. Query the public data safely ordered by newest entries (now tracking landlord responses)
$query = "SELECT * FROM requests WHERE visibility='public' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Forum | GCH Portal</title>
    <link rel="stylesheet" href="../style.css">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .feed { 
            width: 85%; 
            max-width: 1200px;
            margin: 40px auto; 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); 
            gap: 25px; 
            flex: 1 0 auto; /* Sticky footer reinforcement */
        }

        .card-forum { 
            background: white; 
            padding: 24px; 
            border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            border-left: 5px solid #2c3e50; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-forum:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        }

        .card-forum h3 { 
            margin-top: 0; 
            color: #2c3e50; 
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .meta { 
            display: flex; 
            gap: 8px; 
            flex-wrap: wrap; 
            margin-bottom: 15px; 
        }

        .meta span { 
            background: #ecf0f1; 
            color: #2c3e50;
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 0.8em; 
            font-weight: 500;
        }

        .meta .status-pending { background: #ffeaa7; color: #d63031; font-weight: bold; }
        .meta .status-inprogress { background: #dff9fb; color: #0984e3; font-weight: bold; }
        .meta .status-resolved { background: #badc58; color: #6ab04c; font-weight: bold; }

        .card-forum p { 
            color: #4a5568; 
            line-height: 1.6; 
            font-size: 0.95rem;
            margin: 0 0 15px 0;
            word-wrap: break-word;
        }

        /* RESPONSE PRESENTATION CONTAINER & CHILD UI BLOCKS */
        .forum-notes-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .dashboard-landlord-reply {
            background: #fffdf3;
            border-radius: 4px;
            padding: 10px;
            font-size: 0.85rem;
            border-left: 2px solid #d4af37;
        }
        .dashboard-landlord-reply strong { color: #c59b27; display: block; margin-bottom: 2px; }
        .dashboard-landlord-reply p { margin: 0; font-style: italic; color: #2c3e50; }

        .dashboard-admin-reply {
            background: #fdf2f2;
            border-radius: 4px;
            padding: 10px;
            font-size: 0.85rem;
            border-left: 2px solid #c0392b;
        }
        .dashboard-admin-reply strong { color: #c0392b; display: block; margin-bottom: 2px; }
        .dashboard-admin-reply p { margin: 0; font-style: italic; color: #2c3e50; }

        .response-none {
            background: #fdfefe;
            border: 1px dashed #dcdde1;
            color: #7f8c8d;
            font-style: italic;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .card-forum small { 
            display: block; 
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
            color: #718096; 
            font-size: 0.85rem;
        }

        .no-data { 
            grid-column: 1 / -1; 
            text-align: center; 
            padding: 60px; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            color: #7f8c8d; 
        }

        .no-data h3 { color: #2c3e50; margin-bottom: 10px; }
        
        .forum-title-section {
            width: 85%;
            max-width: 1200px;
            margin: 30px auto 0 auto;
        }
        .forum-title-section h1 { color: #2c3e50; margin: 0; font-size: 2rem; }
        .forum-title-section p { color: #718096; margin: 5px 0 0 0; }
        
        footer {
            flex-shrink: 0;
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: auto;
        }
    </style>
</head>

<body>

<header>
    <a href="dashboard.php">
        <img src="../images/lingcode.png" alt="Dashboard" width="103" height="60" style="border-radius:4px;">
    </a>
    <nav>
        <a href="request.php">Submit a Request</a>
        <a href="forum.php" style="color: #d4af37;">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            📝 <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="..\logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<div class="forum-title-section">
    <h1>Community Forum</h1>
    <p>Public maintenance reports shared by fellow dorm residents.</p>
</div>

<div class="feed">

<?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Map all potential tracking states to clean dynamic classes
        $status_lower = strtolower($row['status']);
        if ($status_lower === 'pending') {
            $status_class = 'status-pending';
        } elseif ($status_lower === 'in progress') {
            $status_class = 'status-inprogress';
        } else {
            $status_class = 'status-resolved';
        }

        // Determine if an admin note is present and explicitly set to visible for student logs
        $show_admin_note = (!empty($row['admin_response']) && (int)$row['is_admin_note_visible'] === 1);
        $has_landlord_note = !empty($row['landlord_response']);
?>
        <div class="card-forum">
            <div>
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                <div class="meta">
                    <span><strong>Category:</strong> <?php echo htmlspecialchars($row['request_type']); ?></span>
                    <span><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></span>
                    <span class="<?php echo $status_class; ?>">● <?php echo htmlspecialchars($row['status']); ?></span>
                </div>

                <strong>Details:</strong>
                <p><?php echo nl2br(htmlspecialchars($row['details'])); ?></p>

                <strong>Activity Logs:</strong>
                <div class="forum-notes-container">
                    <?php if ($has_landlord_note): ?>
                        <div class="dashboard-landlord-reply">
                            <strong>🏢 Landlord Management Response:</strong>
                            <p><?php echo nl2br(htmlspecialchars($row['landlord_response'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_admin_note): ?>
                        <div class="dashboard-admin-reply">
                            <strong>🛡️ System Administrator Notice:</strong>
                            <p><?php echo nl2br(htmlspecialchars($row['admin_response'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!$has_landlord_note && !$show_admin_note): ?>
                        <div class="response-none">
                            No updates or notices have been posted on this order yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <small>
                Posted by <strong>@<?php echo htmlspecialchars($row['username']); ?></strong> 
                on <?php echo date('M d, Y • h:i A', strtotime($row['created_at'])); ?>
            </small>
        </div>
<?php
    }
} else {
    echo "<div class='no-data'>
            <h3>No Shared Reports Yet</h3>
            <p>Any service issues flagged as 'public' will stream live into this community canvas panel.</p>
          </div>";
}

mysqli_close($conn);
?>

</div>

<footer>
    <p>&copy; 2026 LingCode | GCH Service Request Portal | CpE-2204</p>
</footer>

<div class="ai-chat-widget" style="margin: 30px auto; max-width: 800px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <h3 style="color: #2c3e50; margin-top: 0;">🤖 LingCode AI Assistant</h3>
    <p style="color: #7f8c8d; font-size: 0.9rem;">Ask our local AI model for help summarizing dorm guidelines or drafts!</p>
    
    <div style="margin-bottom: 15px;">
        <textarea id="ai-prompt-input" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Type your query here (e.g., Help me draft a respectful update about the water leaking issue)..."></textarea>
    </div>
    
    <button type="button" id="btn-ask-ai" style="background: #e67e22; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer;">Ask Local AI</button>
    
    <div id="ai-response-box" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #e67e22; border-radius: 4px; display: none; min-height: 40px; line-height: 1.5; color: #2c3e50;">
        </div>
</div>

</body>
</html>