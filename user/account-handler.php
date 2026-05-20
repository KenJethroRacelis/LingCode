<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// 1. Authorization Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_SESSION['username'];
    $action_type = $_POST['action_type'] ?? '';

    // FORM ACTION TYPE A: UPDATE STRINGS METADATA
    if ($action_type === 'update_profile') {
        $email   = trim($_POST['email']);
        $contact = trim($_POST['contact']);
        $address = trim($_POST['address']);

        if (empty($email)) {
            header("Location: account.php?error=empty");
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET email = ?, contact = ?, address = ? WHERE username = ?");
        $stmt->bind_param("ssss", $email, $contact, $address, $username);
        
        if ($stmt->execute()) {
            header("Location: account.php?success=1");
        } else {
            header("Location: account.php?error=system");
        }
        $stmt->close();
        mysqli_close($conn);
        exit();
    }

    // FORM ACTION TYPE B: SECURE PASSPHRASE OVERRIDE
    if ($action_type === 'update_password') {
        $current_password = $_POST['current_password'];
        $new_password     = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            header("Location: account.php?error=password_mismatch");
            exit();
        }

        // Fetch user's current hashed password from database
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify the provided old password matches the database hash
        if ($user && password_verify($current_password, $user['password'])) {
            // Hash the new password safely using industry standards (BCRYPT)
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $new_hash, $username);
            
            if ($update_stmt->execute()) {
                header("Location: account.php?success=1");
            } else {
                header("Location: account.php?error=system");
            }
            $update_stmt->close();
        } else {
            header("Location: account.php?error=wrong_password");
        }

        mysqli_close($conn);
        exit();
    }
}

// Redirect back to page if accessed via arbitrary URL GET method
header("Location: account.php");
exit();
?>