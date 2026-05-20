<?php
session_start();
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$session_user = $_SESSION['username'];

// Fetch active requests for this logged-in user
$my_requests_query = "SELECT * FROM requests WHERE username = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($my_requests_query);
$stmt->bind_param("s", $session_user);
$stmt->execute();
$my_requests_result = $stmt->get_result();

// --- Chart Data Preparation (Existing Logic) ---
$all_categories = ["Water", "Electricity", "Internet", "Sanitation", "Security", "General"];
$chart_data = array_fill_keys($all_categories, 0);

$query = "SELECT request_type, COUNT(*) as count
          FROM requests 
          WHERE status = 'Pending' 
          GROUP BY request_type";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $type = $row['request_type'];
    if (array_key_exists($type, $chart_data)) {
        $chart_data[$type] = (int)$row['count'];
    }
}

$labels = array_keys($chart_data);
$data = array_values($chart_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LingCode - Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .my-requests-section {
            width: 85%;
            max-width: 1200px;
            margin: 40px auto 10px auto;
        }
        
        .my-requests-section h2 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.8rem;
        }

        .my-requests-section .section-subtitle {
            color: #718096;
            margin: 0 0 20px 0;
            font-size: 0.95rem;
        }

        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .user-request-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            border-left: 4px solid #3498db;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .user-request-card.visibility-private {
            border-left-color: #7f8c8d;
        }

        .user-request-card h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.15rem;
        }

        .card-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .card-badges span {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            background: #e2e8f0;
            color: #4a5568;
        }

        .card-badges .badge-public { background: #e3f2fd; color: #0d47a1; }
        .card-badges .badge-private { background: #f5f5f5; color: #424242; }

        .card-badges .status-pending { background: #ffeaa7; color: #d63031; }
        .card-badges .status-inprogress { background: #dff9fb; color: #0984e3; }
        .card-badges .status-resolved { background: #badc58; color: #6ab04c; }

        .user-request-card p {
            color: #4a5568;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0 0 15px 0;
        }

        /* ACTIVITY LOG CONTAINER & MULTI-REPLY BLOCKS */
        .forum-notes-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
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

        .user-request-card .card-date {
            font-size: 0.8rem;
            color: #a0aec0;
            border-top: 1px dashed #edf2f7;
            padding-top: 10px;
            margin-bottom: 12px;
        }

        /* ACTION PANEL UTILITIES */
        .card-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid #f0f2f5;
            padding-top: 12px;
            margin-top: auto;
        }

        .card-actions form {
            flex: 1;
            margin: 0;
        }

        .btn-action {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #dcdde1;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            background: white;
            color: #2c3e50;
            transition: all 0.2s ease;
        }

        .btn-action-toggle:hover {
            background: #f1f2f6;
            border-color: #7f8c8d;
        }

        .btn-action-delete {
            color: #d63031;
            border-color: #fab1a0;
        }

        .btn-action-delete:hover {
            background: #ff7675;
            color: white;
            border-color: #d63031;
        }

        .empty-requests-box {
            grid-column: 1 / -1;
            background: #fff;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            color: #718096;
        }
    </style>
</head>
<body>

<header>
    <a href="dashboard.php"><img src="../images/lingcode.png" alt="Dashboard" width="103" height="60"></a>
    <nav>
        <a href="request.php">Submit a Request</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            📝 <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<section class="hero">
    <h2><i>LingCode - Naglilingkod sa iyong pagbukod</i></h2>
    <p>Submit requests, track updates, and stay connected with dorm services!</p>
    <button onclick="location.href='request.php'">Submit a Request</button>
</section>

<div class="my-requests-section">
    <h2>My Requests</h2>
    <p class="section-subtitle">Real-time status updates of your submitted tickets.</p>
    
    <div class="requests-grid">
        <?php if ($my_requests_result->num_rows > 0): ?>
            <?php while ($row = $my_requests_result->fetch_assoc()): 
                $status_lower = strtolower($row['status']);
                if ($status_lower === 'pending') {
                    $status_class = 'status-pending';
                } elseif ($status_lower === 'in progress') {
                    $status_class = 'status-inprogress';
                } else {
                    $status_class = 'status-resolved';
                }
                
                $is_public = ($row['visibility'] === 'public');
                $vis_class = $is_public ? 'badge-public' : 'badge-private';
                $card_vis_modifier = !$is_public ? 'visibility-private' : '';

                // Establish administrative notice conditions
                $show_admin_note = (!empty($row['admin_response']) && (int)$row['is_admin_note_visible'] === 1);
                $has_landlord_note = !empty($row['landlord_response']);
            ?>
                <div class="user-request-card <?php echo $card_vis_modifier; ?>">
                    <div>
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        
                        <div class="card-badges">
                            <span class="<?php echo $status_class; ?>">● <?php echo htmlspecialchars($row['status']); ?></span>
                            <span>📍 <?php echo htmlspecialchars($row['location']); ?></span>
                            <span>🔧 <?php echo htmlspecialchars($row['request_type']); ?></span>
                            <span class="<?php echo $vis_class; ?>"><?php echo ucfirst($row['visibility']); ?></span>
                        </div>
                        
                        <p><?php echo nl2br(htmlspecialchars($row['details'])); ?></p>

                        <div class="forum-notes-container">
                            <?php if ($has_landlord_note): ?>
                                <div class="dashboard-landlord-reply">
                                    <strong>🏢 Landlord Update:</strong>
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
                                    No operational responses or notices attached yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="card-date">
                            Logged on <?php echo date('M d, Y • h:i A', strtotime($row['created_at'])); ?>
                        </div>

                        <div class="card-actions">
                            <form action="dashboard-handler.php" method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="toggle_visibility">
                                <button type="submit" class="btn-action btn-action-toggle">
                                    👁️ Make <?php echo $is_public ? 'Private' : 'Public'; ?>
                                </button>
                            </form>

                            <form action="dashboard-handler.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this request?');">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="delete">
                                <button type="submit" class="btn-action btn-action-delete">🗑️ Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-requests-box">
                <p style="font-size:1.1rem; font-weight:bold; margin:0 0 5px 0; color:#2c3e50;">No requests filed yet</p>
                <p style="margin:0;">This is where you will see your submitted requests!</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $stmt->close(); ?>

<div class="container" style="margin-top:20px;">    
    <div class="chart-section">
        <h3>Live Distribution of Pending Requests</h3>
        <p style="color: #666;">Overview of current maintenance workload by category</p>
        <div class="chart-container">
            <canvas id="pendingChart"></canvas>
        </div>
    </div>

    <div class="features">
        <div class="card">
            <h3>Submit a Request</h3>
            <p>Report maintenance issues, cleaning needs, or general concerns</p>
            <a href="request.php">Fill Out a Request Form</a>
        </div>
        <div class="card">
            <h3>Community Forum</h3>
            <p>Check the status of your requests and join other dormers.</p>
            <a href="forum.php">View Dormer Discussions</a>
        </div>
        <div class="card">
            <h3>Helpdesk</h3>
            <p>Browse frequently asked questions or ask for assistance.</p>
            <a href="helpdesk.php">Visit the Helpdesk</a>
        </div>
        <div class="card">
            <h3>Manage Account</h3>
            <p>Update your profile information and preferences.</p>
            <a href="account.php">Update Profile</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; LingCode | GCH Service Request Portal | CpE-2204</p>
</footer>

<script>
const ctx = document.getElementById('pendingChart').getContext('2d');
const pendingChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Number of Pending Requests',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: '#3498db',
            borderColor: '#2980b9',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

</body>
</html>