<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $requested_quantity = intval($_POST['quantity']);
    
    // Check if product exists and is active
    $sql = "SELECT stock, is_active FROM products WHERE name = '$product_name'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $available_stock = intval($product['stock']);
        $is_active = intval($product['is_active']);
        
        // Check if product is still active
        if ($is_active == 0) {
            echo json_encode([
                'success' => false,
                'available' => false,
                'message' => 'This product is no longer available',
                'available_stock' => 0,
                'product_deleted' => true
            ]);
            exit;
        }
        
        // Check if enough stock
        if ($available_stock >= $requested_quantity) {
            echo json_encode([
                'success' => true,
                'available' => true,
                'available_stock' => $available_stock
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'available' => false,
                'available_stock' => $available_stock,
                'message' => "Only $available_stock items available"
            ]);
        }
    } else {
        // Product not found (deleted)
        echo json_encode([
            'success' => false,
            'available' => false,
            'message' => 'Product not found',
            'available_stock' => 0,
            'product_deleted' => true
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>