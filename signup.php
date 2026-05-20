<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create an Account | LingCode</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Structural layout rules mirroring index.php */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-container {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
        }

        /* Left Side: Informational panel tailored for new registrants */
        .info-side {
            flex: 1;
            padding: 60px;
            background: #2c3e50;
            color: white;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 4px solid #c59b27;
        }

        .info-side h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .info-side p {
            font-size: 1.2rem;
            line-height: 1.6;
            color: #bdc3c7;
        }

        /* Right Side: Registration Card Container */
        .form-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f6f9;
        }

        .signup-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            border-top: 5px solid #d4af37; /* Branded gold highlight accent top */
        }

        .signup-card h2 {
            margin-top: 0;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #34495e;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcdde1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        /* Focus highlight utilizing our theme colors */
        input:focus {
            outline: none;
            border-color: #d4af37;
        }

        /* Submit Button using our Slate Blue color scheme */
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background: #1a252f;
        }

        /* Styling for the split button container */
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .btn-role {
            flex: 1;
            padding: 14px 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
            color: white;
        }

        .btn-role:active {
            transform: scale(0.98);
        }

        /* Dormer Button using your Primary Deep Slate Blue */
        .btn-dormer {
            background: #2c3e50;
        }

        .btn-dormer:hover {
            background: #1a252f;
        }

        /* Landlord Button using your Accent Gold */
        .btn-landlord {
            background: #d4af37;
            color: #2c3e50; /* Dark text for clear readability on gold */
        }

        .btn-landlord:hover {
            background: #c59b27;
        }

        /* Stack buttons vertically on mobile screens for better touch targets */
        @media (max-width: 480px) {
            .button-group {
                flex-direction: column;
            }
        }

        /* Redirection back to login screen */
        .login-redirect {
            margin-top: 20px;
            text-align: center;
            border-top: 1px solid #dcdde1;
            padding-top: 15px;
            font-size: 0.95rem;
            color: #7f8c8d;
        }

        .login-redirect a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }

        .login-redirect a:hover {
            color: #d4af37;
        }

        @media (max-width: 850px) {
            .main-container { flex-direction: column; }
            .info-side { padding: 40px; text-align: center; border-right: none; border-bottom: 4px solid #c59b27; }
            .form-side { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="info-side">
        <h1>Join LingCode</h1>
        <p>Create an account to quickly connect with your landlord, lodge maintenance reports, track utilities, and keep up with neighborhood notifications.</p>
    </div>

    <div class="form-side">
        <div class="signup-card">
            <h2>Create Account</h2>
            <?php if (isset($_GET['error'])): ?>
                <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold; font-size: 0.95rem;">
                    <?php 
                        switch ($_GET['error']) {
                            case 'password_mismatch':
                                echo "❌ Passwords do not match. Please try again.";
                                break;
                            case 'username_taken':
                                echo "❌ Username is already registered.";
                                break;
                            case 'email_taken':
                                echo "❌ Email address is already in use.";
                                break;
                            case 'invalid_role':
                                echo "❌ Invalid account role selected.";
                                break;
                            case 'system_error':
                                echo "❌ System error. Please contact admin.";
                                break;
                            default:
                                echo "❌ An error occurred. Please try again.";
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action="signup-handler.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a unique username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="yourname@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="password_confirm" placeholder="Repeat your password" minlength="6" required>
                </div>

                <div class="button-group">
                    <button type="submit" name="register_role" value="user" class="btn-role btn-dormer">
                        Register as Dormer
                    </button>
                    
                    <button type="submit" name="register_role" value="mod" class="btn-role btn-landlord">
                        Register as Landlord
                    </button>
                </div>
            </form>

            <div class="login-redirect">
                Already have an account? <a href="index.php">Sign In here</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>