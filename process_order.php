<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'place_order') {
        $user_id = $_SESSION['user_id'];
        $cart = json_decode($_POST['cart'], true);
        $total = floatval($_POST['total']);
        $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
        
        // Validate cart
        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }
        
        // Get user's delivery address from session or database
        $delivery_address = '';
        if (isset($_SESSION['user_street']) && isset($_SESSION['user_city'])) {
            $delivery_address = $_SESSION['user_street'] . ', ' . $_SESSION['user_city'];
        } else {
            // Fetch from database if not in session
            $user_query = "SELECT street, city FROM users WHERE id = $user_id";
            $user_result = $conn->query($user_query);
            if ($user_result && $user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $delivery_address = $user_data['street'] . ', ' . $user_data['city'];
            }
        }
        
        if (empty($delivery_address)) {
            echo json_encode(['success' => false, 'message' => 'Delivery address not found']);
            exit;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert into orders table
            $order_sql = "INSERT INTO orders (user_id, total_amount, delivery_address, status, created_at) 
                          VALUES ($user_id, $total, '$delivery_address', 'pending', NOW())";
            
            if (!$conn->query($order_sql)) {
                throw new Exception('Error creating order: ' . $conn->error);
            }
            
            $order_id = $conn->insert_id;
            
            // Insert order items
            foreach ($cart as $item) {
                $product_name = mysqli_real_escape_string($conn, $item['name']);
                $quantity = intval($item['quantity']);
                $price = floatval(str_replace(['₱', ','], '', $item['price']));
                
                $item_sql = "INSERT INTO order_items (order_id, product_name, quantity, price) 
                             VALUES ($order_id, '$product_name', $quantity, $price)";
                
                if (!$conn->query($item_sql)) {
                    throw new Exception('Error adding order item: ' . $conn->error);
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Order placed successfully',
                'order_id' => $order_id
            ]);
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>