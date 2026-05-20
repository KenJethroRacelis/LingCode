<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | Lingcode</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
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

        .landing-side {
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

        .landing-side h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .landing-side p {
            font-size: 1.2rem;
            line-height: 1.6;
            color: #bdc3c7;
        }

        .features {
            margin-top: 30px;
            list-style: none;
            padding: 0;
        }

        .features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f6f9;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-card h2 {
            margin-top: 0;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
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
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background: #2980b9;
        }

        .signup-container {
            margin-top: 20px;
            text-align: center;
            border-top: 1px solid #dcdde1;
            padding-top: 20px;
        }

        .signup-container p {
            margin: 0 0 10px 0;
            color: #7f8c8d;
            font-size: 0.95rem;
        }

        .btn-signup {
            display: block;
            text-align: center;
            text-decoration: none;
            padding: 12px;
            background: #d4af37;
            color: #2c3e50;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .btn-signup:hover {
            background: #c59b27;
        }

        @media (max-width: 850px) {
            .main-container { flex-direction: column; }
            .landing-side { padding: 40px; text-align: center; border-right: none; border-bottom: 4px solid #c59b27; }
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="landing-side">
        <h1>LingCode</h1>
        <p>Your direct line to community maintenance and support. Report issues, track progress, and stay informed.</p>
        
        <ul class="features">
            <li>✅ Easy service requests for Water & Power</li>
            <li>✅ Real-time status updates</li>
            <li>✅ Community forum for public concerns</li>
            <li>✅ Direct communication with technicians</li>
        </ul>
    </div>

    <div class="login-side">
        <div class="login-card">
            <h2>Sign In</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert-error">
                    <?php 
                        if($_GET['error'] === 'invalid_credentials') {
                            echo "❌ Invalid matching credentials entered.";
                        } else {
                            echo "❌ System could not locate that account.";
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action="login-handler.php" method="POST">
                <div class="form-group">
                    <label for="login_identity">Username or Email Address</label>
                    <input type="text" id="login_identity" name="login_identity" placeholder="username or email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit">Enter Portal</button>
            </form>

            <div class="signup-container">
                <p>New to the dorm community?</p>
                <a href="signup.php" class="btn-signup">Create an Account</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>