<?php
session_start();

$conn = mysqli_connect("localhost", "cloud_user", "password123", "dormer_info");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture the single input string and password entries directly
    $login_identity = trim($_POST['login_identity']);
    $password       = $_POST['password'];

    if (empty($login_identity) || empty($password)) {
        header("Location: index.php?error=user_not_found");
        exit();
    }

    // 2. Prepare database verification sequence checking username OR email structures
    $stmt = $conn->prepare("SELECT username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $login_identity, $login_identity);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        // 3. Verify standard hashed system signature constraints
        if (password_verify($password, $user_data['password'])) {
            
            // 4. Bind persistent cross-page variable references
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['email']    = $user_data['email'];
            $_SESSION['role']     = $user_data['role'];

            // 5. Route seamlessly based on role identification rules
            switch ($_SESSION['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'mod':
                    header("Location: mod/dashboard.php");
                    break;
                case 'user':
                default:
                    header("Location: user/dashboard.php");
                    break;
            }
            $stmt->close();
            mysqli_close($conn);
            exit();

        } else {
            header("Location: index.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: index.php?error=user_not_found");
        exit();
    }
}

header("Location: index.php");
exit();
?>