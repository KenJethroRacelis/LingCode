<?php
session_start();

// 1. Authorization Guard: Stop unauthorized/logged-out submissions
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// 2. Establish database connection
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Capture the logged-in session username securely
    $username = $_SESSION['username']; 
    
    // 4. Capture typed form data (trim trailing spaces)
    $title        = trim($_POST['title']);
    $request_type = trim($_POST['request_type']);
    $location     = trim($_POST['location']);
    $details      = trim($_POST['details']);
    
    // 5. Checkbox Evaluation
    $visibility = isset($_POST['visibility']) ? 'public' : 'private';

    // 6. SQL Prepared Statement Execution
    $stmt = $conn->prepare("INSERT INTO requests (username, title, request_type, location, details, visibility) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $title, $request_type, $location, $details, $visibility);

    if ($stmt->execute()) {
        // SUCCESS: Redirect back to the request form with a success flag
        $stmt->close();
        mysqli_close($conn);
        header("Location: request.php?success=1");
        exit();
    } else {
        // FAILURE: Redirect back to the request form with an error flag
        $stmt->close();
        mysqli_close($conn);
        header("Location: request.php?error=system_error");
        exit();
    }
} else {
    // If someone tries to access this script via a direct URL GET request, send them away
    header("Location: request.php");
    exit();
}
?>