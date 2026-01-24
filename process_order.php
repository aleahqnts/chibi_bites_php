<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';
require_once 'upload_payment.php';

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
        
        // Handle payment proof upload
        $payment_proof = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadPaymentProof($_FILES['payment_proof']);
            if ($upload_result['success']) {
                $payment_proof = $upload_result['filepath'];
            } else {
                echo json_encode(['success' => false, 'message' => $upload_result['message']]);
                exit;
            }
        }
        
        // Get delivery address
        $delivery_address = '';
        if (isset($_SESSION['user_street']) && isset($_SESSION['user_city'])) {
            $delivery_address = $_SESSION['user_street'] . ', ' . $_SESSION['user_city'];
        } else {
            $user_result = $conn->query("SELECT CONCAT(street, ', ', city) as full_address FROM users WHERE id = $user_id");
            if ($user_result && $user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $delivery_address = $user_data['full_address'];
            }
        }
        
        if (empty($delivery_address)) {
            echo json_encode(['success' => false, 'message' => 'Delivery address not found']);
            exit;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, payment_method, payment_proof, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("idsss", $user_id, $total, $delivery_address, $payment_method, $payment_proof);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
            
            // Add order items
            foreach ($cart as $item) {
                $product_name = mysqli_real_escape_string($conn, $item['name']);
                $quantity = intval($item['quantity']);
                $price = floatval(str_replace(['₱', ','], '', $item['price']));
                
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isid", $order_id, $product_name, $quantity, $price);
                
                if (!$stmt->execute()) {
                    throw new Exception('Error adding order item: ' . $stmt->error);
                }
                
                $stmt->close();
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
            // Delete uploaded file if order failed
            if ($payment_proof && file_exists($payment_proof)) {
                unlink($payment_proof);
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>