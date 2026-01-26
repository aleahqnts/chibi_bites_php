<?php
session_start();

require_once 'db_connect.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';
$success = '';

// Check for logout message
if (isset($_GET['logged_out']) && $_GET['logged_out'] == '1') {
    $success = "You have been successfully logged out";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        // Query for user with admin privileges
        $sql = "SELECT * FROM users WHERE email = '$email' AND is_admin = 1";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $admin['id']; // CRITICAL for user management features
                $_SESSION['admin_username'] = $admin['fullname'];
                $_SESSION['admin_email'] = $admin['email'];
                
                // Redirect to admin dashboard
                header('Location: admin.php');
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password, or you don't have admin privileges";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chibi Bites - Admin Login</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Coiny&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-brown: #7C474A;
            --primary-pink: #FEBBCC;
            --light-pink: #FFE7F0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f5f5dc 0%, rgb(255, 240, 243) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            max-width: 450px;
            width: 100%;
            border: 1px solid #e0e0e0;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }

        .logo-section h1 {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 36px;
            margin-bottom: 5px;
        }

        .logo-section p {
            color: #666;
            font-size: 14px;
        }

        .error-message {
            background: #ffe7e7;
            color: #cc0000;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #ffcccc;
        }

        .info-message {
            background: #e7f3ff;
            color: #0066cc;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 13px;
            border: 1px solid #cce5ff;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-brown);
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-pink);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
        }

        .password-toggle-btn:hover {
            color: var(--primary-brown);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: 2px solid var(--primary-pink);
            border-radius: 25px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-btn:hover {
            background: var(--primary-brown);
            border-color: var(--primary-brown);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary-brown);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .security-note {
            background: var(--light-pink);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .security-note strong {
            color: var(--primary-brown);
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 40px 30px;
            }

            .logo-section h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <img src="images/logo.png" alt="Chibi Bites Logo">
            <h1>Admin Portal</h1>
            <p>Chibi Bites Management System</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="error-message">
            🔒 <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="info-message" style="background: #d4edda; color: #155724; border-color: #c3e6cb;">
            ✓ <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" required autofocus placeholder="admin@chibibites.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-toggle">
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                    <button type="button" class="password-toggle-btn" onclick="togglePassword()">Show</button>
                </div>
            </div>

            <button type="submit" class="login-btn">LOGIN</button>
        </form>

        <div class="back-link">
            <a href="index.html">← Back to Website</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle-btn');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Show';
            }
        }

        // Prevent multiple form submissions
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.querySelector('.login-btn');
            btn.disabled = true;
            btn.textContent = 'LOGGING IN...';
            
            // Re-enable after 3 seconds in case of error
            setTimeout(function() {
                btn.disabled = false;
                btn.textContent = 'LOGIN';
            }, 3000);
        });
    </script>
</body>
</html>