<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';

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
        
        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }
        
        // Get delivery address
        $delivery_address = '';
        if (isset($_SESSION['user_street']) && isset($_SESSION['user_city'])) {
            $delivery_address = $_SESSION['user_street'] . ', ' . $_SESSION['user_city'];
        } else {
            $user_result = $conn->query("CALL GetUserProfile($user_id)");
            if ($user_result && $user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $delivery_address = $user_data['full_address'];
            }
            $conn->next_result(); // Clear result set
        }
        
        if (empty($delivery_address)) {
            echo json_encode(['success' => false, 'message' => 'Delivery address not found']);
            exit;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order using stored procedure
            $stmt = $conn->prepare("CALL CreateOrder(?, ?, ?, ?, @order_id)");
            $stmt->bind_param("idss", $user_id, $total, $delivery_address, $payment_method);
            $stmt->execute();
            $stmt->close();
            
            // Get the order_id
            $result = $conn->query("SELECT @order_id as order_id");
            $row = $result->fetch_assoc();
            $order_id = $row['order_id'];
            
            $conn->next_result(); // Clear result
            
            // Add order items
            foreach ($cart as $item) {
                $product_name = mysqli_real_escape_string($conn, $item['name']);
                $quantity = intval($item['quantity']);
                $price = floatval(str_replace(['₱', ','], '', $item['price']));
                
                $stmt = $conn->prepare("CALL AddOrderItem(?, ?, ?, ?)");
                $stmt->bind_param("isid", $order_id, $product_name, $quantity, $price);
                
                if (!$stmt->execute()) {
                    throw new Exception('Error adding order item: ' . $stmt->error);
                }
                
                $stmt->close();
                $conn->next_result(); // Clear result after each call
            }
            
            // Commit transaction
            $conn->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Order placed successfully',
                'order_id' => $order_id
            ]);
            
        } catch (Exception $e) {
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