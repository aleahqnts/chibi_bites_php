<?php
session_start();
// This is still needed here because auth.php is mainly for AJAX/JSON responses
header('Content-Type: application/json');

// Pull the connection in from our new file
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // SIGNUP
    if ($action === 'signup') {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $street = mysqli_real_escape_string($conn, $_POST['street']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $zipcode = mysqli_real_escape_string($conn, $_POST['zipcode']);
        $password = $_POST['password'];
        
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_sql);
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (fullname, email, phone, street, city, zipcode, password, created_at) 
                VALUES ('$fullname', '$email', '$phone', '$street', '$city', '$zipcode', '$hashed_password', NOW())";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating account: ' . $conn->error]);
        }
        exit;
    }
    
    // LOGIN
    if ($action === 'login') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_street'] = $user['street'];
                $_SESSION['user_city'] = $user['city'];
                $_SESSION['user_zipcode'] = $user['zipcode'];
                $_SESSION['logged_in'] = true;
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful',
                    'user' => [
                        'name' => $user['fullname'],
                        'email' => $user['email'],
                        'address' => $user['street'] . ', ' . $user['city'] . ' ' . $user['zipcode']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
        }
        exit;
    }
    
    // LOGOUT
    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        exit;
    }
    
    // CHECK LOGIN STATUS & GET STATS
    if ($action === 'check') {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            // Get user details
            $user_query = "SELECT * FROM users WHERE id = $user_id";
            $user_result = $conn->query($user_query);
            
            if ($user_result && $user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                
                // Get stats
                $stats_query = "SELECT COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE user_id = $user_id";
                $stats_result = $conn->query($stats_query);
                $stats = $stats_result ? $stats_result->fetch_assoc() : ['order_count' => 0, 'total_spent' => 0];
                
                // Get history
                $history_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
                $history_result = $conn->query($history_query);
                $history = [];
                if ($history_result) {
                    while($row = $history_result->fetch_assoc()) {
                        $history[] = $row;
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user' => [
                        'fullname' => $user['fullname'],
                        'name' => $user['fullname'],
                        'email' => $user['email'],
                        'phone' => $user['phone'],
                        'street' => $user['street'],
                        'city' => $user['city'],
                        'address' => $user['street'] . ', ' . $user['city'],
                        'order_count' => $stats['order_count'],
                        'total_spent' => $stats['total_spent'],
                        'history' => $history
                    ]
                ]);
            }
        } else {
            echo json_encode(['success' => true, 'logged_in' => false]);
        }
        exit;
    }

    // GET PRODUCTS
    if ($action === 'get_products') {
        $sql = "SELECT * FROM products WHERE is_active = 1";
        $result = $conn->query($sql);
        $products = [];
        
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode(['success' => true, 'products' => $products]);
        exit;
    }

    // UPDATE PROFILE
    if ($action === 'update_profile') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $street = mysqli_real_escape_string($conn, $_POST['street']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $zipcode = mysqli_real_escape_string($conn, $_POST['zipcode']);
        
        $sql = "UPDATE users 
                SET fullname = '$fullname', 
                    phone = '$phone', 
                    street = '$street', 
                    city = '$city', 
                    zipcode = '$zipcode' 
                WHERE id = $user_id";
        
        if ($conn->query($sql) === TRUE) {
            // Update session variables
            $_SESSION['user_name'] = $fullname;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_street'] = $street;
            $_SESSION['user_city'] = $city;
            $_SESSION['user_zipcode'] = $zipcode;
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $conn->error]);
        }
        exit;
    }

    // CHANGE PASSWORD (for logged-in users in edit profile)
    if ($action === 'change_password') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = $user_id";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            
            if ($conn->query($update_sql) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        }
        exit;
    }

    // FORGOT PASSWORD - Check if email exists
    if ($action === 'check_email') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Email found']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
        }
        exit;
    }

    // RESET PASSWORD (for forgot password flow)
    if ($action === 'reset_password') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $new_password = $_POST['new_password'];
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error resetting password']);
        }
        exit;
    }
}
?>