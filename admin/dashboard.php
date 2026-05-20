<?php
session_start();
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

// 1. Authorization Guard: Lock page exclusively to systemic administrators
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. Query Shelf A: Fetch all active Admin-Pinned Requests
$pinned_query = "SELECT * FROM requests WHERE is_pinned_admin = 1 ORDER BY created_at DESC";
$pinned_result = mysqli_query($conn, $pinned_query);

// 3. Query Shelf B: Fetch all standard active tickets (Not pinned by Admin and Not Resolved)
$incoming_query = "SELECT * FROM requests WHERE is_pinned_admin = 0 AND status != 'Resolved' ORDER BY created_at DESC";
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
    <title>LingCode - System Admin Engine</title>
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
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 20px;
        }

        .landlord-task-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            border-left: 4px solid #c0392b; /* Admin Signature Dark Red */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        /* Distinctive structural tinting for admin-pinned high-attention cards */
        .landlord-task-card.status-pinned-active {
            border-left-color: #27ae60; /* Emerald green tint priority for admin pins */
            background: #f4fff7;
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

        /* Responses Container Style rules */
        .dashboard-notes-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        .dashboard-landlord-reply {
            background: #fffdf3;
            border-radius: 4px;
            padding: 10px;
            font-size: 0.85rem;
            border-left: 2px solid #e67e22;
        }
        .dashboard-landlord-reply strong { color: #d35400; display: block; margin-bottom: 2px; }
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

        .landlord-task-card .card-date {
            font-size: 0.8rem;
            color: #a0aec0;
            border-top: 1px dashed #edf2f7;
            padding-top: 10px;
            margin-bottom: 12px;
        }

        .card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-top: 1px solid #f0f2f5;
            padding-top: 12px;
            margin-top: auto;
        }

        .card-actions form {
            display: inline-block;
            margin: 0;
        }

        .btn-action {
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
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .btn-action-pin:hover {
            background: #f1f2f6;
            border-color: #27ae60;
            color: #27ae60;
        }

        .btn-action-visibility:hover {
            background: #f1f2f6;
            border-color: #2980b9;
            color: #2980b9;
        }

        .btn-action-respond {
            color: #c0392b;
            border-color: #fadbd8;
        }
        .btn-action-respond:hover {
            background: #c0392b;
            color: white;
            border-color: #962d22;
        }

        .btn-action-delete {
            color: #7f8c8d;
            border-color: #bdc3c7;
        }
        .btn-action-delete:hover {
            background: #d63031;
            color: white;
            border-color: #b2bec3;
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
        <a href="dashboard.php" style="color: #ff6060; font-weight: bold;">System Queue Console</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            🛡️ <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff8585; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<section class="hero" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); border-bottom: 4px solid #c0392b;">
    <h2><i>System Administrator Dashboard</i></h2>
    <p>Oversee ticket visibility & status, audit landlord remarks, or delete system entries.</p>
</section>

<div class="mod-management-section">
    
    <h2>📌 Operational Board</h2>
    <p class="section-subtitle">Exclusive administrative dashboard shelf view</p>
    
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

                        <div class="dashboard-notes-container">
                            <?php if (!empty($row['landlord_response'])): ?>
                                <div class="dashboard-landlord-reply">
                                    <strong>🏢 Landlord Log Note:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row['landlord_response'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($row['admin_response'])): ?>
                                <div class="dashboard-admin-reply">
                                    <strong>🛡️ Your Note (<?php echo ($row['is_admin_note_visible'] == 1) ? 'Visible' : 'Hidden'; ?>):</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row['admin_response'])); ?></p>
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
                                <input type="hidden" name="action_type" value="toggle_admin_pin">
                                <button type="submit" class="btn-action btn-action-pin">📍 Unpin Card</button>
                            </form>

                            <form action="dashboard-handler.php" method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="toggle_visibility">
                                <button type="submit" class="btn-action btn-action-visibility">👁️ Set <?php echo ($row['visibility'] === 'public') ? 'Private' : 'Public'; ?></button>
                            </form>

                            <a href="respond.php?id=<?php echo $row['id']; ?>" class="btn-action btn-action-respond">✍️ Audit/Note</a>

                            <form action="dashboard-handler.php" method="POST" onsubmit="return confirm('Delete this work order from database permanently?');">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="delete_request">
                                <button type="submit" class="btn-action btn-action-delete">🗑️ Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-requests-box">
                <p style="font-size:1rem; margin:0;">Administrative dashboard pin line completely unpopulated.</p>
            </div>
        <?php endif; ?>
    </div>


    <div class="shelf-divider"></div>


    <h2>📥 Request Pipeline</h2>
    <p class="section-subtitle">View pending and in-progress work orders</p>
    
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

                        <div class="dashboard-notes-container">
                            <?php if (!empty($row['landlord_response'])): ?>
                                <div class="dashboard-landlord-reply">
                                    <strong>🏢 Landlord Log Note:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row['landlord_response'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($row['admin_response'])): ?>
                                <div class="dashboard-admin-reply">
                                    <strong>🛡️ Your Note (<?php echo ($row['is_admin_note_visible'] == 1) ? 'Visible' : 'Hidden'; ?>):</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row['admin_response'])); ?></p>
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
                                <input type="hidden" name="action_type" value="toggle_admin_pin">
                                <button type="submit" class="btn-action btn-action-pin">📌 Pin to Board</button>
                            </form>

                            <form action="dashboard-handler.php" method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="toggle_visibility">
                                <button type="submit" class="btn-action btn-action-visibility">👁️ Set <?php echo ($row['visibility'] === 'public') ? 'Private' : 'Public'; ?></button>
                            </form>

                            <a href="respond.php?id=<?php echo $row['id']; ?>" class="btn-action btn-action-respond">✍️ Audit/Note</a>

                            <form action="dashboard-handler.php" method="POST" onsubmit="return confirm('Delete this work order from database permanently?');">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_type" value="delete_request">
                                <button type="submit" class="btn-action btn-action-delete">🗑️ Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-requests-box" style="border-color: #badc58;">
                <p style="font-size:1.1rem; font-weight:bold; margin:0 0 5px 0; color:#27ae60;">🎉 System Matrix Intact</p>
                <p style="margin:0;">No standard outstanding community maintenance requests are pending tracking.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="margin-top:40px;">    
    <div class="chart-section">
        <h3>Pending Request Distribution</h3>
        <p style="color: #666;">Overview of pending requests categorized by request type</p>
        <div class="chart-container">
            <canvas id="pendingChart"></canvas>
        </div>
    </div>
</div>

<footer>
    <p>&copy; LingCode | System Admin Portal | CpE-2204</p>
</footer>

<script>
const ctx = document.getElementById('pendingChart').getContext('2d');
const pendingChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'System Backlog Volume',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: '#c0392b',
            borderColor: '#962d22',
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