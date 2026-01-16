<?php
session_start();
header('Content-Type: application/json');

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $product = [
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'image' => $_POST['image'],
            'quantity' => intval($_POST['quantity'])
        ];
        
        // Check if product already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['name'] === $product['name']) {
                $_SESSION['cart'][$key]['quantity'] += $product['quantity'];
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = $product;
        }
        
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }
    
    if ($action === 'update') {
        $index = intval($_POST['index']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0 && isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = $quantity;
        }
        
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }
    
    if ($action === 'remove') {
        $index = intval($_POST['index']);
        
        if (isset($_SESSION['cart'][$index])) {
            array_splice($_SESSION['cart'], $index, 1);
        }
        
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }
    
    if ($action === 'get') {
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }
    
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        exit;
    }
}

// If no valid action, return error
echo json_encode(['success' => false, 'error' => 'Invalid action']);
exit;
?>