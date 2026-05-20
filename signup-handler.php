<?php
session_start();

// 1. Establish database connection
$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Capture and sanitize inputs
    $username         = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email            = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password         = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Capture the selected role ('user' or 'mod') from the button click
    $role = mysqli_real_escape_string($conn, $_POST['register_role']);

    // 3. Validation Safeguards
    
    // Check if passwords match
    if ($password !== $password_confirm) {
        header("Location: signup.php?error=password_mismatch");
        exit();
    }

    // White-list checking for roles (Prevents someone from manipulating the HTML to inject 'admin')
    $allowed_roles = ['user', 'mod'];
    if (!in_array($role, $allowed_roles)) {
        header("Location: signup.php?error=invalid_role");
        exit();
    }

    // 4. Check if Username or Email already exists in the system
    $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $existing_user = mysqli_fetch_assoc($check_result);
        if ($existing_user['username'] === $username) {
            header("Location: signup.php?error=username_taken");
        } else {
            header("Location: signup.php?error=email_taken");
        }
        exit();
    }

    // 5. Securely Hash the Password
    // This turns plain text (e.g., "myPassword123") into a secure 60-character hash string
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 6. Insert User into Database
    $insert_query = "INSERT INTO users (username, email, password, role) 
                     VALUES ('$username', '$email', '$hashed_password', '$role')";

    if (mysqli_query($conn, $insert_query)) {
        // Success! Automatically log them in by setting session variables
        $_SESSION['username'] = $username;
        $_SESSION['email']    = $email;
        $_SESSION['role']     = $role;

        // Redirect to their specific dashboard folder dynamically
        if ($role === 'mod') {
            header("Location: mod/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        // Fallback for unexpected database errors
        header("Location: signup.php?error=system_error");
        exit();
    }
}
?>