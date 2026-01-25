<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'deduct_stock') {
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $quantity = intval($_POST['quantity']);
        
        // Get current stock
        $check_query = "SELECT id, stock FROM products WHERE name = '$product_name'";
        $result = $conn->query($check_query);
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $current_stock = intval($product['stock']);
            $product_id = $product['id'];
            
            // Check if enough stock
            if ($current_stock >= $quantity) {
                $new_stock = $current_stock - $quantity;
                
                // Update stock
                $update_query = "UPDATE products SET stock = $new_stock WHERE id = $product_id";
                
                if ($conn->query($update_query)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Stock updated',
                        'new_stock' => $new_stock
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error updating stock: ' . $conn->error
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Insufficient stock',
                    'available_stock' => $current_stock
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>