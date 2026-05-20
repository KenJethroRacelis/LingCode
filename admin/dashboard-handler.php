<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

session_start();
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

// 1. Authorization Gatekeeper: Restrict access purely to System Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action     = $_POST['action_type'] ?? '';

    // --- OPERATION A: TOGGLE ADMIN INDEPENDENT FORUM PIN ---
    if ($action === 'toggle_admin_pin') {
        $stmt = $conn->prepare("SELECT is_pinned_admin FROM requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $row = $res->fetch_assoc()) {
            $new_pin_state = ($row['is_pinned_admin'] == 1) ? 0 : 1;
            
            $update_stmt = $conn->prepare("UPDATE requests SET is_pinned_admin = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_pin_state, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $stmt->close();
    }

    // --- OPERATION B: TOGGLE REQUEST PUBLIC / PRIVATE VISIBILITY ---
    elseif ($action === 'toggle_visibility') {
        $stmt = $conn->prepare("SELECT visibility FROM requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $row = $res->fetch_assoc()) {
            $new_visibility = ($row['visibility'] === 'public') ? 'private' : 'public';
            
            $update_stmt = $conn->prepare("UPDATE requests SET visibility = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_visibility, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $stmt->close();
    }

    // --- OPERATION C: PERMANENT SYSTEM PURGE (DELETE ENTRY) ---
    elseif ($action === 'delete_request') {
        $delete_stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
        $delete_stmt->bind_param("i", $request_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }

    // --- OPERATION D: PROCESSING AUDIT OVERRIDES & VISIBLE NOTES ---
    elseif ($action === 'submit_admin_response') {
        $admin_note     = trim($_POST['admin_response']);
        $new_status     = $_POST['ticket_status'];
        // Catch check-box item: if checked, value is '1', otherwise fall back to '0'
        $note_visible   = isset($_POST['is_admin_note_visible']) ? 1 : 0;

        $allowed_statuses = ['Pending', 'In Progress', 'Resolved'];
        if (in_array($new_status, $allowed_statuses)) {
            
            $update_stmt = $conn->prepare("UPDATE requests SET admin_response = ?, status = ?, is_admin_note_visible = ? WHERE id = ?");
            $update_stmt->bind_param("ssii", $admin_note, $new_status, $note_visible, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
}

// Seamless bounce back onto the admin central dashboard panel overview
header("Location: dashboard.php");
exit();
?>