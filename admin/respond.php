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

// 2. Extract and Validate Target ID Token
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$request_id = (int)$_GET['id'];

// 3. Fetch Request Details via Prepared Statement
$query = "SELECT * FROM requests WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Ticket doesn't exist
    header("Location: dashboard.php");
    exit();
}

$ticket = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Request | Admin Console</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .container-form {
            width: 60%;
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 5px solid #c0392b; /* Admin crimson banner override */
        }

        .ticket-summary-box {
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .ticket-summary-box h3 {
            margin: 0 0 12px 0;
            color: #2c3e50;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 15px;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 12px;
        }

        .original-details {
            font-size: 0.95rem;
            color: #4a5568;
            line-height: 1.6;
            background: #ffffff;
            padding: 12px;
            border-radius: 4px;
            border-left: 3px solid #cbd5e0;
            white-space: pre-wrap;
            margin-bottom: 15px;
        }

        .existing-landlord-note {
            font-size: 0.85rem;
            background: #fffdf5;
            padding: 12px;
            border-radius: 4px;
            border-left: 3px solid #e67e22;
            color: #2c3e50;
        }
        .existing-landlord-note strong { color: #d35400; }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 20px;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }

        textarea {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 1em;
            transition: border-color 0.3s;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
            border-color: #c0392b;
        }

        /* Toggle Box Segment for Admin Note Visibility */
        .visibility-toggle-group {
            background: #f4fff7;
            border: 1px solid #d4edda;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        .visibility-toggle-group input {
            width: 20px;
            height: 20px;
            cursor: pointer;
            margin: 0;
        }
        .visibility-toggle-group .toggle-label-text h7 {
            font-weight: bold;
            color: #1e7e34;
            display: block;
            font-size: 0.95rem;
        }
        .visibility-toggle-group .toggle-label-text p {
            margin: 2px 0 0 0;
            font-size: 0.8rem;
            color: #5a6268;
        }

        /* Group Radio Selectors for Status Changes */
        .status-options-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
            background: #fdf2f2;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #fadbd8;
        }

        .radio-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .radio-option input {
            margin-top: 3px;
            cursor: pointer;
            width: auto;
        }

        .radio-option div {
            font-size: 0.95rem;
            color: #2c3e50;
        }

        .radio-option p {
            margin: 2px 0 0 0;
            font-size: 0.8rem;
            color: #718096;
        }

        .action-row {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-submit {
            flex: 2;
            padding: 14px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #1a252f;
            color: #c0392b;
        }

        .btn-cancel {
            flex: 1;
            padding: 14px;
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
        }

        @media (max-width: 850px) {
            .container-form { width: 90%; margin: 20px auto; }
        }
    </style>
</head>
<body>

<header>
    <a href="dashboard.php"><img src="../images/lingcode.png" alt="Dashboard" width="103" height="60"></a>
    <nav>
        <a href="dashboard.php" style="color: #ff6060;">System Queue Console</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            🛡️ <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff8585; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<div class="container-form">
    <h2>Audit Override Request #<?php echo $ticket['id']; ?></h2>
    <p style="color:#718096; margin-top:-10px; margin-bottom:20px;">Review system tickets, check team comments, and append administrative directives.</p>

    <div class="ticket-summary-box">
        <h3><?php echo htmlspecialchars($ticket['title']); ?></h3>
        
        <div class="meta-grid">
            <div>👤 <strong>Reporter:</strong> @<?php echo htmlspecialchars($ticket['username']); ?></div>
            <div>📍 <strong>Location:</strong> <?php echo htmlspecialchars($ticket['location']); ?></div>
            <div>🔧 <strong>Category:</strong> <?php echo htmlspecialchars($ticket['request_type']); ?></div>
            <div>👁️ <strong>Visibility:</strong> <?php echo ucfirst($ticket['visibility']); ?></div>
        </div>

        <div class="original-details"><?php echo htmlspecialchars($ticket['details']); ?></div>

        <?php if (!empty($ticket['landlord_response'])): ?>
            <div class="existing-landlord-note">
                <strong>🏢 Landlord Note:</strong>
                <span style="font-style: italic;">"<?php echo htmlspecialchars($ticket['landlord_response']); ?>"</span>
            </div>
        <?php endif; ?>
    </div>

    <form action="dashboard-handler.php" method="POST">
        <input type="hidden" name="request_id" value="<?php echo $ticket['id']; ?>">
        <input type="hidden" name="action_type" value="submit_admin_response">

        <label for="admin_response">Admin Remarks</label>
        <textarea id="admin_response" name="admin_response" rows="5" placeholder="Provide strategic system overrides, structural scheduling audits, or audit annotations..." required><?php echo htmlspecialchars($ticket['admin_response'] ?? ''); ?></textarea>

        <label class="visibility-toggle-group">
            <input type="checkbox" name="is_admin_note_visible" value="1" <?php echo (isset($ticket['is_admin_note_visible']) && $ticket['is_admin_note_visible'] == 1) ? 'checked' : ''; ?>>
            <div class="toggle-label-text">
                <h7>🌐 Publish Note to Community Channels</h7>
                <p>By default, administrative remarks are hidden from residents. Toggle this check box to make this note visible on the student forum screen.</p>
            </div>
        </label>

        <label>Update Processing Status</label>
        <div class="status-options-group">
            <label class="radio-option">
                <input type="radio" name="ticket_status" value="Pending" <?php echo ($ticket['status'] === 'Pending') ? 'checked' : ''; ?>>
                <div>
                    <strong>Keep Pending</strong>
                    <p>Enforce hold state. Ticket remains in the backlog for operational adjustments or scheduling.</p>
                </div>
            </label>

            <label class="radio-option">
                <input type="radio" name="ticket_status" value="In Progress" <?php echo ($ticket['status'] === 'In Progress') ? 'checked' : ''; ?>>
                <div>
                    <strong>Set to In Progress</strong>
                    <p>Mark as actively managed. Informs both the landlord and student segments that tasks are underway.</p>
                </div>
            </label>

            <label class="radio-option">
                <input type="radio" name="ticket_status" value="Resolved" <?php echo ($ticket['status'] === 'Resolved') ? 'checked' : ''; ?>>
                <div>
                    <strong>Mark as Resolved</strong>
                    <p>Force administrative closure. Relocates this item entirely out of active operational queues.</p>
                </div>
            </label>
        </div>

        <div class="action-row">
            <a href="dashboard.php" class="btn-cancel">Back</a>
            <button type="submit" class="btn-submit">Commit Changes</button>
        </div>
    </form>
</div>

<footer>
    <p>&copy; © LingCode | System Admin Portal | CpE-2204</p>
</footer>

</body>
</html>