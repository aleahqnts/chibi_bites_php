<?php
session_start();

// Simple hardcoded admin credentials - CHANGE THESE!
// In production, use hashed passwords in database
$ADMIN_USERNAME = "admin";
$ADMIN_PASSWORD = "admin123"; // CHANGE THIS!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid username or password";
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

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary-brown);
            text-decoration: none;
            font-size: 14px;
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

        <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">LOGIN</button>
        </form>

        <div class="back-link">
            <a href="index.html">← Back to Website</a>
        </div>
    </div>
</body>
</html>