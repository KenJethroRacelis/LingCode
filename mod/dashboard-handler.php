<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

session_start();
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

// 1. Authorization Gatekeeper
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mod') {
    header("Location: ../index.php");
    exit();
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action     = $_POST['action_type'];

    if ($action === 'toggle_pin') {
        // --- OPERATION A: TOGGLE FORUM PIN VALUE ---
        // Fetch current pinning state safely
        $stmt = $conn->prepare("SELECT is_pinned FROM requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $row = $res->fetch_assoc()) {
            // Flip bits natively
            $new_pin_state = ($row['is_pinned'] == 1) ? 0 : 1;
            
            $update_stmt = $conn->prepare("UPDATE requests SET is_pinned = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_pin_state, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $stmt->close();

    } elseif ($action === 'submit_response') {
        // --- OPERATION B: EXECUTE PROGRESSION UPDATE ---
        $response_note = trim($_POST['landlord_response']);
        $new_status    = $_POST['ticket_status'];

        // Enforce database validation rules on statuses
        $allowed_statuses = ['Pending', 'In Progress', 'Resolved'];
        if (in_array($new_status, $allowed_statuses)) {
            
            $update_stmt = $conn->prepare("UPDATE requests SET landlord_response = ?, status = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $response_note, $new_status, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
}

// Seamless bounce back right onto main structural command console overview page
header("Location: dashboard.php");
exit();
?>