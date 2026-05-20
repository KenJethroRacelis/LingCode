<?php
session_start();
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

// 1. Authorization Guard: Lock page exclusively to mods
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mod') {
    header("Location: ../index.php");
    exit();
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. Query Shelf A: Fetch all active Pinned Requests
$pinned_query = "SELECT * FROM requests WHERE is_pinned = 1 ORDER BY created_at DESC";
$pinned_result = mysqli_query($conn, $pinned_query);

// 3. Query Shelf B: Fetch all standard outstanding (Unpinned) active tickets
$incoming_query = "SELECT * FROM requests WHERE is_pinned = 0 AND status != 'Resolved' ORDER BY created_at DESC";
$incoming_result = mysqli_query($conn, $incoming_query);


// --- Chart Data Preparation Engine ---
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
    <title>LingCode - Landlord Control Center</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .mod-management-section {
            width: 85%;
            max-width: 1200px;
            margin: 40px auto 10px auto;
        }
        
        .mod-management-section h2 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.8rem;
        }

        .mod-management-section .section-subtitle {
            color: #718096;
            margin: 0 0 20px 0;
            font-size: 0.95rem;
        }

        .shelf-divider {
            border-bottom: 2px solid #e2e8f0;
            margin: 30px 0;
        }

        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 20px;
        }

        .landlord-task-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            border-left: 4px solid #e67e22; /* Warning orange for active queues */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        /* Distinctive structural tinting for pinned high-attention cards */
        .landlord-task-card.status-pinned-active {
            border-left-color: #9b59b6; /* Purple tint priority */
            background: #fbf8ff;
        }

        .landlord-task-card h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 1.15rem;
            padding-right: 25px;
        }

        .reporter-tag {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 10px;
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

        .landlord-task-card p {
            color: #4a5568;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0 0 15px 0;
        }

        .dashboard-landlord-reply {
            background: #fffdf3;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            border-left: 2px solid #d4af37;
        }
        .dashboard-landlord-reply strong { color: #c59b27; display: block; margin-bottom: 2px; }
        .dashboard-landlord-reply p { margin: 0; font-style: italic; color: #2c3e50; }

        .landlord-task-card .card-date {
            font-size: 0.8rem;
            color: #a0aec0;
            border-top: 1px dashed #edf2f7;
            padding-top: 10px;
            margin-bottom: 12px;
        }

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
            text-align: center;
            text-decoration: none;
            display: inline-block;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .btn-action-pin:hover {
            background: #f1f2f6;
            border-color: #9b59b6;
            color: #9b59b6;
        }

        .btn-action-respond {
            color: #3498db;
            border-color: #aed6f1;
        }

        .btn-action-respond:hover {
            background: #3498db;
            color: white;
            border-color: #2980b9;
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
        <a href="respond.php" style="color: #e67e22; font-weight: bold;">Work Orders Queue</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            🏠 <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<section class="hero" style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);">
    <h2><i>LingCode Landlord Dashboard</i></h2>
    <p>Monitor community request distributions, pin priority workload operations, and submit task adjustments.</p>
</section>

<div class="mod-management-section">
    
    <h2>📌 Pinned Active Assignments</h2>
    <p class="section-subtitle">Issues flagged here alert residents that maintenance tasks are actively underway.</p>
    
    <div class="requests-grid">
        <?php if (mysqli_num_rows($pinned_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($pinned_result)): 
                $status_lower = strtolower($row['status']);
                $status_class = ($status_lower === 'pending') ? 'status-pending' : 'status-inprogress';
                $vis_class = ($row['visibility'] === 'public') ? 'badge-public' : 'badge-private';
            ?>
                <div class="landlord-task-card status-pinned-active">
                    <div>
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <div class="reporter-tag">Filed by: @<?php echo htmlspecialchars($row['username']); ?></div>
                        
                        <div class="card-badges">
                            <span class="<?php echo $status_class; ?>">● <?php echo htmlspecialchars($row['status']); ?></span>
                            <span>📍 <?php echo htmlspecialchars($row['location']); ?></span>
                            <span>🔧 <?php echo htmlspecialchars($row['request_type']); ?></span>
                            <span class="<?php echo $vis_class; ?>"><?php echo ucfirst($row['visibility']); ?></span>
                        </div>
                        
                        <p><?php echo nl2br(htmlspecialchars($row['details'])); ?></p>

                        <?php if (!empty($row['landlord_response'])): ?>
                            <div class="dashboard-landlord-reply">
                                <strong>🏢 Your Current Note:</strong>
                                <p><?php echo nl2br(htmlspecialchars($row['landlord_response'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <div class="card-date">
                            Logged on <?php echo date('M d, Y • h:i A', strtotime($row['created_at'])); ?>
                        </div>

                        <div class="card-actions">
                            <form action="dashboard-handler.php" method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="toggle_pin">
                                <button type="submit" class="btn-action btn-action-pin">📍 Unpin Order</button>
                            </form>

                            <a href="respond.php?id=<?php echo $row['id']; ?>" class="btn-action btn-action-respond">✍️ Update Status</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-requests-box">
                <p style="font-size:1rem; margin:0;">No items pinned. Pin tickets from the pipeline below to show residents they are being addressed.</p>
            </div>
        <?php endif; ?>
    </div>


    <div class="shelf-divider"></div>


    <h2>📥 Incoming Request Backlog</h2>
    <p class="section-subtitle">Unresolved requests requiring initial evaluations or diagnostic remarks.</p>
    
    <div class="requests-grid">
        <?php if (mysqli_num_rows($incoming_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($incoming_result)): 
                $status_lower = strtolower($row['status']);
                $status_class = ($status_lower === 'pending') ? 'status-pending' : 'status-inprogress';
                $vis_class = ($row['visibility'] === 'public') ? 'badge-public' : 'badge-private';
            ?>
                <div class="landlord-task-card">
                    <div>
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <div class="reporter-tag">Filed by: @<?php echo htmlspecialchars($row['username']); ?></div>
                        
                        <div class="card-badges">
                            <span class="<?php echo $status_class; ?>">● <?php echo htmlspecialchars($row['status']); ?></span>
                            <span>📍 <?php echo htmlspecialchars($row['location']); ?></span>
                            <span>🔧 <?php echo htmlspecialchars($row['request_type']); ?></span>
                            <span class="<?php echo $vis_class; ?>"><?php echo ucfirst($row['visibility']); ?></span>
                        </div>
                        
                        <p><?php echo nl2br(htmlspecialchars($row['details'])); ?></p>

                        <?php if (!empty($row['landlord_response'])): ?>
                            <div class="dashboard-landlord-reply">
                                <strong>🏢 Your Current Note:</strong>
                                <p><?php echo nl2br(htmlspecialchars($row['landlord_response'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <div class="card-date">
                            Logged on <?php echo date('M d, Y • h:i A', strtotime($row['created_at'])); ?>
                        </div>

                        <div class="card-actions">
                            <form action="dashboard-handler.php" method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="toggle_pin">
                                <button type="submit" class="btn-action btn-action-pin">📌 Pin to Board</button>
                            </form>

                            <a href="respond.php?id=<?php echo $row['id']; ?>" class="btn-action btn-action-respond">✍️ Respond</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-requests-box" style="border-color: #badc58;">
                <p style="font-size:1.1rem; font-weight:bold; margin:0 0 5px 0; color:#6ab04c;">🎉 Queue Fully Clear!</p>
                <p style="margin:0;">All community maintenance tickets have been reviewed or resolved.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="margin-top:40px;">    
    <div class="chart-section">
        <h3>System Distribution of Pending Requests</h3>
        <p style="color: #666;">Global overview of current backlog volume assigned by task category</p>
        <div class="chart-container">
            <canvas id="pendingChart"></canvas>
        </div>
    </div>
</div>

<footer>
    <p>&copy; LingCode | Landlord Administration Portal | CpE-2204</p>
</footer>

<script>
const ctx = document.getElementById('pendingChart').getContext('2d');
const pendingChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Backlog Tickets',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: '#e67e22',
            borderColor: '#d35400',
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