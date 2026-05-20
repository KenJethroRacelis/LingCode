<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// 1. Authorization Guard: Ensure only admins access this panel
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 2. Fetch current admin profile data to populate inputs
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$session_user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT email, contact, address FROM users WHERE username = ?");
$stmt->bind_param("s", $session_user);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage System Admin Account | System Admin Portal</title>
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
        .container-account {
            width: 90%;
            max-width: 700px;
            margin: 40px auto;
            flex: 1 0 auto;
        }
        .account-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            border-top: 4px solid #2c3e50;
        }
        .account-card.accent {
            border-top-color: #c0392b; /* Admin crimson accent line */
        }
        .account-card.danger-zone {
            border-top-color: #d63031;
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
            color: #34495e;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcdde1;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #c0392b;
        }
        input[readonly] {
            background-color: #eaeded;
            color: #7f8c8d;
            cursor: not-allowed;
        }
        .btn-save {
            margin-top: 20px;
            padding: 12px 25px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-save:hover {
            background: #1a252f;
        }
        .btn-warn {
            background: #c0392b;
            color: white;
        }
        .btn-warn:hover {
            background: #962d22;
        }
        .btn-danger {
            background: #d63031;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-danger:hover {
            background: #b2bec3;
            color: #d63031;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        footer {
            flex-shrink: 0;
            margin-top: auto;
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
        }
    </style>
</head>
<body>

<header>
    <a href="dashboard.php">
        <img src="../images/lingcode.png" alt="Dashboard" width="103" height="60" style="border-radius:4px;">
    </a>
    <nav>
        <a href="dashboard.php">System Queue Console</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="color: #ff6060; border-left: 1px solid #7f8c8d; padding-left: 15px;">
            🛡️ <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff8585; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<div class="container-account">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">🎉 Admin profile configuration applied successfully.</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php
                switch($_GET['error']) {
                    case 'wrong_password': echo "❌ Incorrect current credential verification target."; break;
                    case 'password_mismatch': echo "❌ Administrative security configuration confirmation failed."; break;
                    case 'empty': echo "❌ Explicit profile input parameters cannot be left blank."; break;
                    default: echo "❌ System processing failure. Please contact database admin.";
                }
            ?>
        </div>
    <?php endif; ?>

    <div class="account-card">
        <h2>Admin Profile Information</h2>
        <form action="account-handler.php" method="POST">
            <input type="hidden" name="action_type" value="update_profile">

            <label>Administrative Identifier</label>
            <input type="text" value="<?php echo htmlspecialchars($session_user); ?>" readonly>

            <label for="email">Work Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>

            <label for="contact">Office Contact Number</label>
            <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($user_data['contact'] ?? ''); ?>" placeholder="e.g., Office Local / Mobile">

            <label for="address">HQ / Administrative Office Station</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>" placeholder="e.g., Main Property Management Desk Office">

            <button type="submit" class="btn-save">Save Profile Changes</button>
        </form>
    </div>

    <div class="account-card accent">
        <h2>Change System Password</h2>
        <form action="account-handler.php" method="POST">
            <input type="hidden" name="action_type" value="update_password">

            <label for="current-password">Current Password</label>
            <input type="password" id="current-password" name="current_password" required>

            <label for="new-password">New Secure Password</label>
            <input type="password" id="new-password" name="new_password" minlength="6" required>

            <label for="confirm-password">Confirm New Secure Password</label>
            <input type="password" id="confirm-password" name="confirm_password" minlength="6" required>

            <button type="submit" class="btn-save btn-warn">Update Administrative Password</button>
        </form>
    </div>

    <div class="account-card danger-zone">
        <h2>Session Control</h2>
        <p style="color:#7f8c8d; font-size:0.95rem; margin-top:0;">
            Ready to close the system administration console? Ensure all response logs have been saved before exiting.
        </p>
        <a href="../logout.php" class="btn-danger">Log Out of Console</a>
    </div>

</div>

<footer>
    <p>&copy; LingCode | System Admin Portal | CpE-2204</p>
</footer>

</body>
</html>