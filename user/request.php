<?php
session_start();

// 1. Authorization Guard: If not logged in or if they aren't a regular user/dormer, kick them out
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Optional: Grab error or success status flags passed by your future backend processing engine
$success_msg = isset($_GET['success']) ? "🎉 Request submitted successfully!" : "";
$error_msg = isset($_GET['error']) ? "❌ Error processing request. Please try again." : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit a Request | GCH Service Request Portal</title>
    <link rel="stylesheet" href="../style.css">
    
    <style>
        /* Specific layout constraint just for the focused form page layout wrapper */
        .container-form {
            width: 60%;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 5px solid #d4af37; /* Consistent gold top accent line */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        input, select, textarea {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #d4af37; /* Accent gold focus lines */
        }

        textarea {
            resize: vertical;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }
        
        .checkbox-container label {
            margin: 0;
            cursor: pointer;
        }

        /* Form Submission button using signature branding colors */
        .btn-submit {
            margin-top: 25px;
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
            color: #d4af37; /* Turns text gold on hover */
        }

        @media (max-width: 850px) {
            .container-form { width: 90%; margin: 20px auto; }
        }
    </style>
</head>

<body>

<header>
    <a href="dashboard.php">
        <img src="../images/lingcode.png" alt="Dashboard" width="103" height="60" style="border-radius:4px;">
    </a>

    <nav>
        <a href="request.php" style="color: #d4af37;">Submit a Request</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            📝 <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="..\logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<div class="container-form">

    <h2>Service Request Form</h2>

    <?php if ($success_msg): ?>
        <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold;">
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold;">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <form action="request-handler.php" method="POST">

        <label for="title">Request Title</label>
        <input type="text" id="title" name="title" placeholder="e.g., Leaking water pipe in bathroom" required>

        <label for="request_type">Request Type</label>
        <select id="request_type" name="request_type" required>
            <option value="" disabled selected>-- Select a category --</option>
            <option value="Electricity">Electricity</option>
            <option value="Water">Water</option>
            <option value="Internet">Internet</option>
            <option value="Sanitation">Sanitation</option>
            <option value="Security">Security</option> 
            <option value="General">General</option>
        </select>

        <label for="location">Room Number / Location Details</label>
        <input type="text" id="location" name="location" placeholder="e.g., Room 204-B, 2nd Floor" required>

        <label for="details">Request Details</label>
        <textarea id="details" name="details" rows="6" placeholder="Describe the problem clearly so the maintenance crew knows what tools to bring..." required></textarea>

        <div class="checkbox-container">
            <input type="checkbox" id="visibility" name="visibility" value="public">
            <label for="visibility">Share request publicly on community forum?</label>
        </div>

        <button type="submit" class="btn-submit">Submit Request</button>
    </form>

</div>

<footer>
    <p>&copy; 2026 GCH Service Request Portal | CpE-2204</p>
</footer>

</body>
</html>