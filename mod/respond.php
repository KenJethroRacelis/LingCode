<?php
session_start();
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

// 1. Authorization Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mod') {
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
    <title>Respond to Order | Landlord Console</title>
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
            border-top: 5px solid #e67e22; /* Institutional landlord orange banner */
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
        }

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
            border-color: #e67e22;
        }

        /* Group Radio Selectors for Status Changes */
        .status-options-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
            background: #fffdf5;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #fef3c7;
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
            color: #e67e22;
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
        <a href="dashboard.php" style="color: #e67e22;">Work Orders Queue</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            Hi Staff, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<div class="container-form">
    <h2>Update Request #<?php echo $ticket['id']; ?></h2>
    <p style="color:#718096; margin-top:-10px; margin-bottom:20px;">Review details and issue staff progress reports.</p>

    <div class="ticket-summary-box">
        <h3><?php echo htmlspecialchars($ticket['title']); ?></h3>
        
        <div class="meta-grid">
            <div>👤 <strong>Reporter:</strong> @<?php echo htmlspecialchars($ticket['username']); ?></div>
            <div>📍 <strong>Location:</strong> <?php echo htmlspecialchars($ticket['location']); ?></div>
            <div>🔧 <strong>Category:</strong> <?php echo htmlspecialchars($ticket['request_type']); ?></div>
            <div>👁️ <strong>Visibility:</strong> <?php echo ucfirst($ticket['visibility']); ?></div>
        </div>

        <div class="original-details"><?php echo htmlspecialchars($ticket['details']); ?></div>
    </div>

    <form action="dashboard-handler.php" method="POST">
        <input type="hidden" name="request_id" value="<?php echo $ticket['id']; ?>">
        <input type="hidden" name="action_type" value="submit_response">

        <label for="landlord_response">Staff Progress Report / Reply Note</label>
        <textarea id="landlord_response" name="landlord_response" rows="5" placeholder="Type specific execution notices, appointment times, or solution declarations..." required><?php echo htmlspecialchars($ticket['landlord_response'] ?? ''); ?></textarea>

        <label>Update Work Order Processing Status</label>
        <div class="status-options-group">
            <label class="radio-option">
                <input type="radio" name="ticket_status" value="Pending" <?php echo ($ticket['status'] === 'Pending') ? 'checked' : ''; ?>>
                <div>
                    <strong>Keep Pending</strong>
                    <p>Ticket remains in the pipeline for future scheduling or diagnostic evaluation checks.</p>
                </div>
            </label>

            <label class="radio-option">
                <input type="radio" name="ticket_status" value="In Progress" <?php echo ($ticket['status'] === 'In Progress') ? 'checked' : ''; ?>>
                <div>
                    <strong>Set to In Progress</strong>
                    <p>Let the resident know that the issue was acknowledged and is being addressed.</p>
                </div>
            </label>

            <label class="radio-option">
                <input type="radio" name="ticket_status" value="Resolved" <?php echo ($ticket['status'] === 'Resolved') ? 'checked' : ''; ?>>
                <div>
                    <strong>Mark as Resolved</strong>
                    <p>Work order is fully completed. This moves the ticket out of your active control dashboard pipeline.</p>
                </div>
            </label>
        </div>

        <div class="action-row">
            <a href="dashboard.php" class="btn-cancel">Back</a>
            <button type="submit" class="btn-submit">Save Status Updates</button>
        </div>
    </form>
</div>

<footer>
    <p>&copy; 2026 GCH Service Request Portal | Landlord Core Engine</p>
</footer>

</body>
</html>