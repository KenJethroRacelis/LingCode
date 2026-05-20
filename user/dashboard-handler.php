<?php
session_start();

// Authorization Guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$session_user = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action     = $_POST['action_type'];

    // Security Verification: Ensure the logged-in user owns this specific request
    $verify_stmt = $conn->prepare("SELECT id, visibility FROM requests WHERE id = ? AND username = ?");
    $verify_stmt->bind_param("is", $request_id, $session_user);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows > 0) {
        $row = $verify_result->fetch_assoc();

        if ($action === 'delete') {
            // Secure Delete Execution
            $delete_stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
            $delete_stmt->bind_param("i", $request_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            
        } elseif ($action === 'toggle_visibility') {
            // Flip Visibility States smoothly
            $new_visibility = ($row['visibility'] === 'public') ? 'private' : 'public';
            
            $update_stmt = $conn->prepare("UPDATE requests SET visibility = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_visibility, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
    $verify_stmt->close();
}

// Bounce back to the dashboard to instantly show the updated states
header("Location: dashboard.php");
exit();
?>