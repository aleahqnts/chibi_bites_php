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
    }
    
    // LOGOUT
    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }
    
    // CHECK LOGIN STATUS
    if ($action === 'check') {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT fullname, email, phone, street, city FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'fullname' => $user['fullname'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'street' => $user['street'],
                    'city' => $user['city']
                ]
            ]);
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
}
?>