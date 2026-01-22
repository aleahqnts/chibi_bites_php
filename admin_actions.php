<?php
session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // UPDATE ORDER STATUS (Direct SQL - No Stored Procedure)
    if ($action === 'update_status') {
        $order_id = intval($_POST['order_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Validate status
        $valid_statuses = ['pending', 'confirmed', 'processing', 'ready', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'];
        
        if (!in_array($status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        // Check if order exists and get current status
        $check_query = "SELECT id, status FROM orders WHERE id = $order_id";
        $check_result = $conn->query($check_query);
        
        if (!$check_result || $check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        $current_order = $check_result->fetch_assoc();
        $old_status = $current_order['status'];
        
        // Check if order can be updated (not cancelled or delivered)
        if ($old_status === 'cancelled' || $old_status === 'delivered') {
            echo json_encode([
                'success' => false, 
                'message' => "Cannot change status from $old_status"
            ]);
            exit;
        }
        
        // Update the order status directly
        $update_query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
        
        if ($conn->query($update_query)) {
            $message = "Order status updated from $old_status to $status";
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'order_id' => $order_id,
                'old_status' => $old_status,
                'new_status' => $status
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Database error: ' . $conn->error
            ]);
        }
        
        exit;
    }
    
    // ADD PRODUCT
    if ($action === 'add_product') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = floatval($_POST['price']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $image_path = mysqli_real_escape_string($conn, $_POST['image_path']);
        
        $sql = "INSERT INTO products (name, price, description, category, image_path, is_active) 
                VALUES ('$name', $price, '$description', '$category', '$image_path', 1)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Product added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
    
    // UPDATE PRODUCT
    if ($action === 'update_product') {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = floatval($_POST['price']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $image_path = mysqli_real_escape_string($conn, $_POST['image_path']);
        
        $sql = "UPDATE products 
                SET name = '$name', price = $price, description = '$description', image_path = '$image_path'
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
    
    // TOGGLE PRODUCT STATUS
    if ($action === 'toggle_product') {
        $id = intval($_POST['id']);
        $is_active = intval($_POST['is_active']);
        $new_status = $is_active ? 0 : 1;
        
        $sql = "UPDATE products SET is_active = $new_status WHERE id = $id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Product status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
    
    // DELETE ORDER (soft delete by setting status to 'deleted')
    if ($action === 'delete_order') {
        $order_id = intval($_POST['order_id']);
        
        $sql = "UPDATE orders SET status = 'deleted' WHERE id = $order_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
}

// GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // GET ORDER DETAILS
    if ($action === 'get_order') {
        $order_id = intval($_GET['order_id']);
        
        // Get order with user info
        $order_query = "
            SELECT o.*, u.fullname, u.email, u.phone 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = $order_id
        ";
        $order_result = $conn->query($order_query);
        
        if ($order_result && $order_result->num_rows > 0) {
            $order = $order_result->fetch_assoc();
            
            // Get order items
            $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
            $items_result = $conn->query($items_query);
            
            $items = [];
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
            
            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
        }
        exit;
    }
    
    // GET USER DETAILS
    if ($action === 'get_user') {
        $user_id = intval($_GET['user_id']);
        
        $user_query = "SELECT * FROM users WHERE id = $user_id";
        $user_result = $conn->query($user_query);
        
        if ($user_result && $user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            
            // Get user's orders
            $orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
            $orders_result = $conn->query($orders_query);
            
            $orders = [];
            while ($order = $orders_result->fetch_assoc()) {
                $orders[] = $order;
            }
            
            // Get statistics
            $stats_query = "
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_spent
                FROM orders 
                WHERE user_id = $user_id AND status != 'cancelled'
            ";
            $stats_result = $conn->query($stats_query);
            $stats = $stats_result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'user' => $user,
                'orders' => $orders,
                'stats' => $stats
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        exit;
    }
    
    // GET PRODUCT DETAILS
    if ($action === 'get_product') {
        $product_id = intval($_GET['product_id']);
        
        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        exit;
    }
    
    // GET SALES REPORT
    if ($action === 'sales_report') {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        $report_query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total_amount) as revenue
            FROM orders
            WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
            AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ";
        
        $result = $conn->query($report_query);
        $report = [];
        
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
        
        echo json_encode(['success' => true, 'report' => $report]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>