<?php
session_start();

// Simple admin authentication - you should enhance this
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db_connect.php';

// Get date range from query parameters or use defaults
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get all orders with user information within date range
$orders_query = "
    SELECT o.*, u.fullname, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
    ORDER BY o.created_at DESC
";
$orders_result = $conn->query($orders_query);

// Get statistics (excluding cancelled and refunded from revenue)
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status IN ('pending', 'confirmed', 'processing', 'ready', 'out_for_delivery') THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN status NOT IN ('cancelled', 'refunded') THEN total_amount ELSE 0 END) as total_revenue,
        COUNT(DISTINCT user_id) as total_customers
    FROM orders
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);

// Get all products
$products_query = "SELECT * FROM products ORDER BY id ASC";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chibi Bites - Admin</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Coiny&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-brown: #6b4b50;
            --primary-pink: #FEBBCC;
            --light-pink: #FFE7F0;
            --accent-green: #9aa559;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background-color: #6d5256; 
            box-shadow: 0 5px 7px rgba(0, 0, 0, 0.1);
            border-bottom: 4px solid pink;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;          
            z-index: 1000;
        }

        .navbar p{
            color:white;
            font-weight: 600;
            font-size: 18px;
            margin-bottom:2px;
        }

        .admin-header {
            background: var(--primary-brown);
            color: var(--white);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.4);
            border-bottom: 3px solid pink;
            margin-bottom: 60px;
        }

        .admin-header h1 {
            font-family: 'Coiny';
            font-size: 23px;
        }

        .admin-header img{
            height: 35px; 
            width: auto;
            margin-left: 15px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .logo-img {
            height: 33px;
            width: auto;
        }

        .title-img {
            margin-top: 2px;
            height: 20px;
            width: auto;
        }

        .logout-btn {
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: white;
            transform: translateY(-2px);
        }

        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
            margin-top:120px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid var(--primary-pink);
        }

        .stat-number {
            font-family: 'Coiny', cursive;
            font-size: 36px;
            color: var(--primary-brown);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 30px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn.active {
            color: var(--primary-brown);
            border-bottom-color: var(--primary-pink);
        }

        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--light-pink);
            color: var(--primary-brown);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-out_for_delivery { background: #bee5eb; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #e2e3e5; color: #383d41; }

        #usersTable td:last-child {
            white-space: normal; /* Allow wrapping for the cell */
            min-width: 150px; /* Ensure minimum width for buttons */
        }

        #usersTable .action-btn {
            display: block; /* Stack buttons vertically */
            width: 100%; /* Full width of cell */
            margin-bottom: 8px; /* Space between buttons */
            margin-right: 0; /* Remove horizontal margin */
            white-space: nowrap; /* Prevent text wrapping inside button */
            min-width: 130px; /* Minimum width for button text */
            padding: 8px 20px; /* Add more horizontal padding */
        }

        #usersTable .action-btn:last-child {
            margin-bottom: 0; /* No margin on last button */
        }

        /* Ensure button text doesn't wrap */
        #usersTable .action-btn.delete {
            white-space: nowrap;
        }

        .action-btn {
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: none;
            padding: 8px 15px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--primary-brown);
            color: white;
        }

        .action-btn.view {
            background: #e7f3ff;
            color: #0066cc;
        }

        .action-btn.delete {
            background: #ffe7e7;
            color: #cc0000;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            border-left: 5px solid pink;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-brown);
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-pink);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-pink);
            color: var(--primary-brown);
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: var(--primary-brown);
            color: white;
        }

        .order-items {
            background: var(--light-pink);
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .order-items h4 {
            color: var(--primary-brown);
            margin-bottom: 10px;
        }

        .order-item {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-family: 'Montserrat', sans-serif;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-pink);
        }

        .filter-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: var(--primary-pink);
            border-color: var(--primary-pink);
            color: var(--primary-brown);
        }

        /* Success Modal Styles */
        .success-modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
        }

        .success-modal-content .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .success-modal-content .close-modal:hover {
            color: #333;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #155724;
        }

        .success-icon svg {
            width: 48px;
            height: 48px;
            fill: #155724;
        }

        .success-modal-content h2 {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .success-modal-content p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .success-modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .success-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s;
            font-size: 14px;
        }

        .success-btn.ok {
            background: var(--accent-green);
            color: var(--white);
        }

        .success-btn.ok:hover {
            background: #a8b561;
            transform: translateY(-2px);
        }

        .success-btn.update-again {
            
            color: gray;
            border: 2px solid gray
        }

        .success-btn.update-again:hover {
            background: gray;
            color:white;
            transform: translateY(-2px);
        }

        /* Success Modal Overlay Styles */
        .success-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            justify-content: center;
            align-items: center;
        }

        .success-modal-overlay.active {
            display: flex;
        }

        .success-modal-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: #9aa559;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-modal-icon svg {
            width: 35px;
            height: 35px;
            fill: white;
        }

        .success-modal-title {
            font-family: 'Coiny';
            color: var(--primary-brown);
            font-size: 28px;
            text-align: center;
            margin-bottom: 15px;
        }

        .success-modal-text {
            font-family: 'Montserrat', sans-serif;
            color: black;
            text-align: center;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .success-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            font-size: 28px;
            color: var(--primary-brown);
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
            opacity: 0.6;
            border: none;
        }

        .success-modal-close:hover {
            background-color: var(--primary-brown);
            color: white;
            opacity: 1;
        }

        .success-modal-btn {
            padding: 15px 20px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid;
            background-color: #9aa559;
            color: white;
            border-color: #9aa559;
        }

        .success-modal-btn:hover {
            background-color: pink;
            border-color: pink;
            color: var(--primary-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .success-modal-overlay .success-modal-content {
            border-left: 5px solid pink;
            border-right: 5px solid pink;
        }

 
               /* Logout Modal Styles */
        .logout-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .logout-modal-overlay.active {
            display: flex;
        }

        .logout-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logout-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            font-size: 28px;
            color: var(--primary-brown);
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
            opacity: 0.6;
            border: none;
        }

        .logout-modal-close:hover {
            background-color: var(--primary-brown);
            border:none;
            opacity: 1;
        }

        .logout-modal-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #fff5f5 0%, #ffebee 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-modal-icon svg {
            width: 35px;
            height: 35px;
            fill: #d32f2f;
        }

        .logout-modal-title {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 28px;
            text-align: center;
            margin-bottom: 15px;
        }

        .logout-modal-text {
            font-family: 'Montserrat', sans-serif;
            color: black;
            text-align: center;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .logout-modal-buttons {
            display: flex;
            gap: 15px;
        }

        .logout-modal-btn {
            flex: 1;
            padding: 15px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid;
            text-transform: uppercase;
        }

        .logout-modal-btn-cancel {
            background-color: #f5f5f5;
            color: #666;
            border-color: #e0e0e0;
        }

        .logout-modal-btn-cancel:hover {
            background-color: #e0e0e0;
            border-color: #bdbdbd;
            transform: translateY(-2px);
            color: #666;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .logout-modal-btn-confirm {
            background-color: #d32f2f;
            color: white;
            border-color: #d32f2f;
        }

        .logout-modal-btn-confirm:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }

               @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .tabs {
                flex-wrap: wrap;
            }

            .tab-btn {
                padding: 10px 15px;
                font-size: 14px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px 5px;
            }

            .success-modal-buttons {
                flex-direction: column;
            }

            .success-btn {
                width: 100%;
            }

            .logout-modal-content {
                padding: 30px 20px;
            }

            .logout-modal-buttons {
                flex-direction: column;
            }
        }

        /* Deactivate/Activate Modal Styles */
        .toggle-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .toggle-modal-overlay.active {
            display: flex;
        }

        .toggle-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease;
        }

        .toggle-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            font-size: 28px;
            color: var(--primary-brown);
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
            opacity: 0.6;
            border: none;
        }

        .toggle-modal-close:hover {
            background-color: var(--primary-brown);
            color: white;
            opacity: 1;
        }

        .toggle-modal-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-modal-icon svg {
            width: 35px;
            height: 35px;
            fill: #856404;
        }

        .toggle-modal-title {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 28px;
            text-align: center;
            margin-bottom: 15px;
        }

        .toggle-modal-text {
            font-family: 'Montserrat', sans-serif;
            color: black;
            text-align: center;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .toggle-modal-buttons {
            display: flex;
            gap: 15px;
        }

        .toggle-modal-btn {
            flex: 1;
            padding: 15px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid;
            text-transform: uppercase;
        }

        .toggle-modal-btn-cancel {
            background-color: #f5f5f5;
            color: #666;
            border-color: #e0e0e0;
        }

        .toggle-modal-btn-cancel:hover {
            background-color: #e0e0e0;
            border-color: #bdbdbd;
            transform: translateY(-2px);
            color: #666;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .toggle-modal-btn-confirm {
            background-color: #ffeaa7;
            color: #6b4b50;
            border-color: #ffeaa7;
        }

        .toggle-modal-btn-confirm:hover {
            background-color: #fbde80;
            border-color: #fbde80;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(133, 100, 4, 0.3);
        }

        .upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: var(--light-pink);
            border: 2px dashed var(--primary-pink);
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #6b4b50;
            transition: all 0.3s;
        }

        .upload-label:hover {
            background: var(--primary-pink);
            color: var(--primary-brown);
        }

        .upload-label svg {
            width: 24px;
            height: 24px;
        }

        #addProductModal .modal-content {
            max-height: 90vh;
            overflow-y: auto;
            margin-top:30px;
        }

        /* Delete Product Modal Styles */
        .delete-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .delete-modal-overlay.active {
            display: flex;
        }

        .delete-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease;
        }

        .delete-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            font-size: 28px;
            color: var(--primary-brown);
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
            opacity: 0.6;
            border: none;
        }

        .delete-modal-close:hover {
            background-color: var(--primary-brown);
            color: white;
            opacity: 1;
        }

        .delete-modal-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #fff5f5 0%, #ffebee 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-modal-icon svg {
            width: 35px;
            height: 35px;
            fill: #d32f2f;
        }

        .delete-modal-title {
            font-family: 'Coiny', cursive;
            color: var(--primary-brown);
            font-size: 28px;
            text-align: center;
            margin-bottom: 15px;
        }

        .delete-modal-text {
            font-family: 'Montserrat', sans-serif;
            color: black;
            text-align: center;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .delete-modal-buttons {
            display: flex;
            gap: 15px;
        }

        .delete-modal-btn {
            flex: 1;
            padding: 15px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid;
            text-transform: uppercase;
        }

        .delete-modal-btn-cancel {
            background-color: #f5f5f5;
            color: #666;
            border-color: #e0e0e0;
        }

        .delete-modal-btn-cancel:hover {
            background-color: #e0e0e0;
            border-color: #bdbdbd;
            transform: translateY(-2px);
            color: #666;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .delete-modal-btn-confirm {
            background-color: #d32f2f;
            color: white;
            border-color: #d32f2f;
        }

        .delete-modal-btn-confirm:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }

        .action-btn.toggle {
            background: #fff3cd;
            color: #856404;
        }

        .action-btn.toggle:hover {
            background: #856404;
            color: white;
        }
        
        /* Date Range Filter Styles */
        .date-range-filter {
            position: fixed;
            right: -320px;
            top: 120px;
            width: 320px;
            background: white;
            padding: 25px;
            border-radius: 15px 0 0 15px;
            box-shadow: -2px 2px 15px rgba(0,0,0,0.15);
            transition: right 0.3s ease;
            z-index: 999;
            border-left: 4px solid var(--primary-pink);
        }

        .date-range-filter.open {
            right: 0;
        }

        .date-range-toggle {
            position: absolute;
            left: -100px;
            top: 20px;
            background: var(--primary-brown);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px 0 0 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            box-shadow: -2px 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }

        .date-range-toggle:hover {
            background: var(--accent-green);
            transform: translateX(-3px);
        }

        .date-range-filter h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary-brown);
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .date-inputs {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .date-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .date-group label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary-brown);
            font-size: 14px;
        }

        .date-group input[type="date"] {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            color: var(--primary-brown);
            transition: border-color 0.3s;
            width: 100%;
        }

        .date-group input[type="date"]:focus {
            outline: none;
            border-color: var(--primary-pink);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }

        .filter-apply-btn,
        .filter-reset-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 20px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-apply-btn {
            background: var(--accent-green);
            color: white;
        }

        .filter-apply-btn:hover {
            background: #8a9549;
            transform: translateY(-2px);
        }

        .filter-reset-btn {
            background: #f5f5f5;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .filter-reset-btn:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .date-range-filter {
                right: -280px;
                width: 280px;
                padding: 20px;
                top: 100px;
            }
            
            .date-range-toggle {
                left: -90px;
                padding: 10px 15px;
                font-size: 12px;
            }
        }

        /* ============================================
   MOBILE RESPONSIVE STYLES - ADMIN PAGE
   ============================================ */

/* Tablets and smaller (968px and below) */
@media screen and (max-width: 968px) {
    .admin-container {
        margin-top: 100px;
        padding: 0 15px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        padding: 20px 15px;
    }
    
    .stat-number {
        font-size: 28px;
    }
    
    .stat-label {
        font-size: 12px;
    }
    
    .tabs {
        gap: 5px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    
    .tabs::-webkit-scrollbar {
        display: none;
    }
    
    .tab-btn {
        padding: 12px 20px;
        font-size: 13px;
        white-space: nowrap;
    }
    
    .tab-content {
        padding: 20px 15px;
        overflow-x: auto;
    }
    
    table {
        font-size: 11px;
        min-width: 800px;
    }
    
    th, td {
        padding: 10px 8px;
    }
    
    .action-btn {
        padding: 6px 10px;
        font-size: 11px;
        margin-right: 3px;
        margin-bottom: 5px;
    }
    
    .search-box input {
        font-size: 14px;
        padding: 10px 15px;
    }
    
    .filter-group {
        gap: 8px;
    }
    
    .filter-btn {
        padding: 6px 15px;
        font-size: 12px;
    }
    
    .modal-content {
        padding: 25px 20px;
        max-height: 85vh;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px;
        font-size: 14px;
    }
    
    .submit-btn {
        padding: 12px;
        font-size: 14px;
    }
    
    .date-range-filter {
        width: 280px;
        right: -280px;
        padding: 20px;
        top: 100px;
    }
    
    .date-range-toggle {
        left: -90px;
        padding: 10px 15px;
        font-size: 12px;
    }
    
    .logout-modal-content,
    .toggle-modal-content,
    .delete-modal-content {
        padding: 35px 25px;
        width: 92%;
    }
    
    .success-modal-content {
        padding: 35px 25px;
    }
}

/* Tablets (768px and below) */
@media screen and (max-width: 768px) {
    .navbar {
        padding: 12px 20px;
        height: 65px;
        flex-direction: row;
    }
    
    .navbar p {
        font-size: 16px;
    }
    
    .brand {
        gap: 8px;
    }
    
    .logo-img {
        height: 30px;
    }
    
    .title-img {
        height: 18px;
    }
    
    .logout-btn {
        padding: 8px 20px;
        font-size: 13px;
    }
    
    .admin-container {
        margin-top: 90px;
        padding: 0 12px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        padding: 18px 15px;
    }
    
    .stat-number {
        font-size: 26px;
    }
    
    .stat-label {
        font-size: 11px;
    }
    
    .tabs {
        gap: 5px;
        margin-bottom: 15px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .tab-btn {
        padding: 10px 15px;
        font-size: 12px;
        border-bottom-width: 2px;
    }
    
    .tab-content {
        padding: 18px 12px;
        border-radius: 12px;
    }
    
    .search-box {
        margin-bottom: 15px;
    }
    
    .search-box input {
        padding: 10px 15px;
        font-size: 13px;
        border-radius: 20px;
    }
    
    .filter-group {
        gap: 6px;
        margin-bottom: 15px;
    }
    
    .filter-btn {
        padding: 6px 12px;
        font-size: 11px;
    }
    
    table {
        font-size: 10px;
        min-width: 700px;
    }
    
    th, td {
        padding: 8px 6px;
    }
    
    .status-badge {
        padding: 4px 8px;
        font-size: 9px;
    }
    
    .action-btn {
        padding: 5px 8px;
        font-size: 10px;
        border-radius: 12px;
    }
    
    #usersTable .action-btn {
        padding: 6px 12px;
        font-size: 10px;
        min-width: 100px;
    }
    
    .modal-content {
        padding: 25px 18px;
        max-height: 80vh;
        border-radius: 18px;
    }
    
    .modal-header h2 {
        font-size: 22px;
    }
    
    .close-modal {
        font-size: 26px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        font-size: 13px;
        margin-bottom: 6px;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px;
        font-size: 13px;
    }
    
    .submit-btn {
        padding: 12px;
        font-size: 13px;
    }
    
    .date-range-filter {
        width: 260px;
        right: -260px;
        padding: 18px;
        top: 90px;
    }
    
    .date-range-filter h3 {
        font-size: 16px;
        margin-bottom: 15px;
    }
    
    .date-group label {
        font-size: 13px;
    }
    
    .date-group input[type="date"] {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .filter-apply-btn,
    .filter-reset-btn {
        padding: 10px 15px;
        font-size: 13px;
    }
    
    .date-range-toggle {
        left: -85px;
        padding: 8px 12px;
        font-size: 11px;
    }
    
    .logout-modal-content,
    .toggle-modal-content,
    .delete-modal-content {
        padding: 30px 20px;
    }
    
    .logout-modal-icon,
    .toggle-modal-icon,
    .delete-modal-icon {
        width: 65px;
        height: 65px;
    }
    
    .logout-modal-icon svg,
    .toggle-modal-icon svg,
    .delete-modal-icon svg {
        width: 32px;
        height: 32px;
    }
    
    .logout-modal-title,
    .toggle-modal-title,
    .delete-modal-title {
        font-size: 24px;
    }
    
    .logout-modal-text,
    .toggle-modal-text,
    .delete-modal-text {
        font-size: 14px;
        margin-bottom: 25px;
    }
    
    .logout-modal-btn,
    .toggle-modal-btn,
    .delete-modal-btn {
        padding: 12px;
        font-size: 14px;
    }
    
    .success-modal-content {
        padding: 30px 20px;
    }
    
    .success-icon {
        width: 70px;
        height: 70px;
    }
    
    .success-modal-content h2 {
        font-size: 22px;
    }
    
    .success-modal-content p {
        font-size: 14px;
        margin-bottom: 25px;
    }
    
    .success-btn {
        padding: 10px 20px;
        font-size: 13px;
    }
    
    .success-modal-icon {
        width: 65px;
        height: 65px;
    }
    
    .success-modal-icon svg {
        width: 32px;
        height: 32px;
    }
    
    .success-modal-title {
        font-size: 24px;
    }
    
    .success-modal-text {
        font-size: 14px;
        margin-bottom: 25px;
    }
    
    .success-modal-btn {
        padding: 12px 18px;
        font-size: 14px;
    }
    
    .upload-label {
        padding: 12px;
        font-size: 14px;
    }
    
    .order-items {
        padding: 12px;
    }
    
    .order-items h4 {
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .order-item {
        padding: 8px;
        font-size: 12px;
    }
}

/* Mobile phones (480px and below) */
@media screen and (max-width: 480px) {
    .navbar {
        padding: 10px 15px;
        height: 60px;
    }
    
    .navbar p {
        font-size: 14px;
        display: none;
    }
    
    .brand {
        gap: 6px;
    }
    
    .logo-img {
        height: 28px;
    }
    
    .title-img {
        height: 16px;
    }
    
    .logout-btn {
        padding: 7px 15px;
        font-size: 12px;
    }
    
    .admin-container {
        margin-top: 80px;
        padding: 0 10px;
    }
    
    .stats-grid {
        gap: 10px;
        margin-bottom: 18px;
    }
    
    .stat-card {
        padding: 15px 12px;
    }
    
    .stat-number {
        font-size: 24px;
    }
    
    .stat-label {
        font-size: 10px;
    }
    
    .tabs {
        gap: 3px;
    }
    
    .tab-btn {
        padding: 8px 12px;
        font-size: 11px;
    }
    
    .tab-content {
        padding: 15px 10px;
    }
    
    .search-box input {
        padding: 9px 12px;
        font-size: 12px;
    }
    
    .filter-group {
        gap: 5px;
    }
    
    .filter-btn {
        padding: 5px 10px;
        font-size: 10px;
    }
    
    table {
        font-size: 9px;
        min-width: 650px;
    }
    
    th, td {
        padding: 6px 4px;
    }
    
    .status-badge {
        padding: 3px 6px;
        font-size: 8px;
    }
    
    .action-btn {
        padding: 4px 6px;
        font-size: 9px;
        margin-right: 2px;
        margin-bottom: 4px;
    }
    
    #usersTable .action-btn {
        padding: 5px 10px;
        font-size: 9px;
        min-width: 90px;
        margin-bottom: 6px;
    }
    
    .modal-content {
        padding: 20px 15px;
        max-height: 75vh;
        border-radius: 15px;
    }
    
    .modal-header {
        margin-bottom: 15px;
    }
    
    .modal-header h2 {
        font-size: 20px;
    }
    
    .close-modal {
        font-size: 24px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
    
    .form-group label {
        font-size: 12px;
        margin-bottom: 5px;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 9px;
        font-size: 12px;
        border-radius: 8px;
    }
    
    .submit-btn {
        padding: 11px;
        font-size: 12px;
    }
    
    .date-range-filter {
        width: 240px;
        right: -240px;
        padding: 15px;
        top: 80px;
    }
    
    .date-range-filter h3 {
        font-size: 15px;
        margin-bottom: 12px;
    }
    
    .date-inputs {
        gap: 12px;
    }
    
    .date-group {
        gap: 6px;
    }
    
    .date-group label {
        font-size: 12px;
    }
    
    .date-group input[type="date"] {
        padding: 9px 10px;
        font-size: 12px;
    }
    
    .filter-buttons {
        gap: 8px;
        margin-top: 8px;
    }
    
    .filter-apply-btn,
    .filter-reset-btn {
        padding: 9px 12px;
        font-size: 12px;
    }
    
    .date-range-toggle {
        left: -80px;
        padding: 7px 10px;
        font-size: 10px;
    }
    
    .logout-modal-content,
    .toggle-modal-content,
    .delete-modal-content {
        padding: 25px 18px;
        width: 95%;
    }
    
    .logout-modal-close,
    .toggle-modal-close,
    .delete-modal-close {
        width: 32px;
        height: 32px;
        font-size: 24px;
    }
    
    .logout-modal-icon,
    .toggle-modal-icon,
    .delete-modal-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 15px;
    }
    
    .logout-modal-icon svg,
    .toggle-modal-icon svg,
    .delete-modal-icon svg {
        width: 30px;
        height: 30px;
    }
    
    .logout-modal-title,
    .toggle-modal-title,
    .delete-modal-title {
        font-size: 22px;
        margin-bottom: 12px;
    }
    
    .logout-modal-text,
    .toggle-modal-text,
    .delete-modal-text {
        font-size: 13px;
        margin-bottom: 22px;
        line-height: 1.5;
    }
    
    .logout-modal-buttons,
    .toggle-modal-buttons,
    .delete-modal-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .logout-modal-btn,
    .toggle-modal-btn,
    .delete-modal-btn {
        width: 100%;
        padding: 11px;
        font-size: 13px;
    }
    
    .success-modal-content {
        padding: 25px 18px;
        width: 95%;
    }
    
    .success-icon {
        width: 65px;
        height: 65px;
        margin-bottom: 15px;
    }
    
    .success-icon svg {
        width: 44px;
        height: 44px;
    }
    
    .success-modal-content h2 {
        font-size: 20px;
        margin-bottom: 12px;
    }
    
    .success-modal-content p {
        font-size: 13px;
        margin-bottom: 22px;
    }
    
    .success-modal-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .success-btn {
        width: 100%;
        padding: 10px;
        font-size: 12px;
    }
    
    .success-modal-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 15px;
    }
    
    .success-modal-icon svg {
        width: 30px;
        height: 30px;
    }
    
    .success-modal-close {
        width: 32px;
        height: 32px;
        font-size: 24px;
    }
    
    .success-modal-title {
        font-size: 22px;
        margin-bottom: 12px;
    }
    
    .success-modal-text {
        font-size: 13px;
        margin-bottom: 22px;
    }
    
    .success-modal-btn {
        padding: 11px 16px;
        font-size: 13px;
    }
    
    .upload-label {
        padding: 10px;
        font-size: 13px;
        gap: 8px;
    }
    
    .upload-label svg {
        width: 20px;
        height: 20px;
    }
    
    #imagePreview img {
        max-width: 150px;
    }
    
    .order-items {
        padding: 10px;
    }
    
    .order-items h4 {
        font-size: 13px;
    }
    
    .order-item {
        padding: 6px;
        font-size: 11px;
    }
}

/* Extra small phones (375px and below) */
@media screen and (max-width: 375px) {
    .navbar {
        padding: 8px 12px;
        height: 55px;
    }
    
    .logo-img {
        height: 26px;
    }
    
    .title-img {
        height: 14px;
    }
    
    .logout-btn {
        padding: 6px 12px;
        font-size: 11px;
    }
    
    .admin-container {
        margin-top: 75px;
    }
    
    .stat-number {
        font-size: 22px;
    }
    
    .stat-label {
        font-size: 9px;
    }
    
    .tab-btn {
        padding: 7px 10px;
        font-size: 10px;
    }
    
    table {
        font-size: 8px;
    }
    
    .date-range-filter {
        width: 220px;
        right: -220px;
    }
}

/* Landscape orientation adjustments */
@media screen and (max-height: 600px) and (orientation: landscape) {
    .admin-container {
        margin-top: 80px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .modal-content {
        max-height: 90vh;
    }
    
    .date-range-filter {
        top: 70px;
    }
}
    </style>
</head>
<body>
    <nav class="navbar">
    <div class="brand">
        <a href="index.html"><img src="images/logo.png" alt="Logo" class="logo-img"></a>
        <a href="index.html"><img src="images/title.png" alt="Website Title" class="title-img"></a>
        <p>Admin Dashboard</p>
    </div>

        <button class="logout-btn" onclick="openLogoutModal()">Logout</button>
    </nav>

    <div class="admin-container">
        <!-- Date Range Filter -->
        <div class="date-range-filter" id="dateRangeFilter">
            <button class="date-range-toggle" onclick="toggleDateFilter()">Date Filter    </button>
            <h3>Filter by Date Range</h3>
            <div class="date-inputs">
                <div class="date-group">
                    <label>From:</label>
                    <input type="date" id="startDate" value="<?php echo $start_date; ?>">
                </div>
                <div class="date-group">
                    <label>To:</label>
                    <input type="date" id="endDate" value="<?php echo $end_date; ?>">
                </div>
                <div class="filter-buttons">
                    <button class="filter-apply-btn" onclick="applyDateFilter()">Apply</button>
                    <button class="filter-reset-btn" onclick="resetDateFilter()">Reset</button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="statTotalOrders"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statPendingOrders"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Orders in Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statDeliveredOrders"><?php echo $stats['delivered_orders']; ?></div>
                <div class="stat-label">Delivered Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statTotalRevenue">₱<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="statTotalCustomers"><?php echo $stats['total_customers']; ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('orders')">Orders</button>
            <button class="tab-btn" onclick="switchTab('users')">Users</button>
            <button class="tab-btn" onclick="switchTab('products')">Products</button>
        </div>

        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content active">
            <div class="search-box">
                <input type="text" id="orderSearch" placeholder="Search by Order ID, Customer Name, or Email..." onkeyup="filterOrders()">
            </div>

            <div class="filter-group">
                <button class="filter-btn active" onclick="filterByStatus('all')">All</button>
                <button class="filter-btn" onclick="filterByStatus('pending')">Pending</button>
                <button class="filter-btn" onclick="filterByStatus('confirmed')">Confirmed</button>
                <button class="filter-btn" onclick="filterByStatus('processing')">Processing</button>
                <button class="filter-btn" onclick="filterByStatus('ready')">Ready</button>
                <button class="filter-btn" onclick="filterByStatus('out_for_delivery')">Out for Delivery</button>
                <button class="filter-btn" onclick="filterByStatus('delivered')">Delivered</button>
                <button class="filter-btn" onclick="filterByStatus('cancelled')">Cancelled</button>
                <button class="filter-btn" onclick="filterByStatus('refunded')">Refunded</button>
            </div>

            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                    <tr class="order-row" data-status="<?php echo $order['status']; ?>">
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo strtoupper($order['payment_method']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo str_replace('_', ' ', $order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button class="action-btn view" onclick="viewOrder(<?php echo $order['id']; ?>)">View</button>
                            <button class="action-btn" onclick="updateOrderStatus(<?php echo $order['id']; ?>)">Update Status</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Users Tab -->
        <div id="users-tab" class="tab-content">
            <div class="search-box">
                <input type="text" id="userSearch" placeholder="Search by Name or Email..." onkeyup="filterUsers()">
            </div>

            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Admin Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr class="user-row">
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['street'] . ', ' . $user['city']); ?></td>
                        <td>
                            <span class="status-badge <?php echo (isset($user['is_admin']) && $user['is_admin']) ? 'status-confirmed' : 'status-pending'; ?>" id="admin-badge-<?php echo $user['id']; ?>">
                                <?php echo (isset($user['is_admin']) && $user['is_admin']) ? 'Admin' : 'User'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="action-btn" onclick="toggleAdminStatus(<?php echo $user['id']; ?>, <?php echo isset($user['is_admin']) ? $user['is_admin'] : 0; ?>)" id="admin-btn-<?php echo $user['id']; ?>">
                                <?php echo (isset($user['is_admin']) && $user['is_admin']) ? 'Revoke Admin' : 'Grant Admin'; ?>
                            </button>
                            <span style="display: inline-block; width: 10px;"></span>
                            <button class="action-btn delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['fullname']); ?>')">Delete User</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Products Tab -->
        <div id="products-tab" class="tab-content">
            <button class="action-btn" onclick="addProduct()" style="margin-bottom: 20px;">+ Add New Product</button>

            <table id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                <tbody>
                        <?php while($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                    
                            <td><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></td>

                             <td>
                                <span style="font-weight: 600; color: <?php echo $product['stock'] > 0 ? '#155724' : '#721c24'; ?>">
                                    <?php echo $product['stock']; ?> 
                                </span>
                            </td>

                            <td>
                                <span class="status-badge <?php echo $product['is_active'] ? 'status-delivered' : 'status-cancelled'; ?>">
                                    <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn" onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                                <button class="action-btn toggle" onclick="toggleProductStatus(<?php echo $product['id']; ?>, <?php echo $product['is_active']; ?>)">
                                    <?php echo $product['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                                <button class="action-btn delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
            </table>
        </div>
    </div>

    <!-- Update Order Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form id="statusForm">
                <input type="hidden" id="statusOrderId">
                
                <div class="form-group">
                    <label>Order ID</label>
                    <input type="text" id="displayOrderId" readonly>
                </div>

                <div class="form-group">
                    <label>New Status</label>
                    <select id="newStatus" required>
                        <option value="">Select Status...</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="ready">Ready</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Update Status</button>
            </form>
        </div>
    </div>

    <!-- View Order Modal -->
    <div id="viewOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button class="close-modal" onclick="closeModal('viewOrderModal')">&times;</button>
            </div>
            <div id="orderDetails"></div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <button class="close-modal" onclick="closeModal('editProductModal')">&times;</button>
            </div>
            <form id="editProductForm">
                <input type="hidden" id="editProductId">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="editProductName" required>
                </div>

                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" id="editProductPrice" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="editProductDescription" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Stock Quantity</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="button" onclick="adjustEditStock(-1)" style="width: 40px; height: 40px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-size: 20px;">−</button>
                        <input type="number" id="editProductStock" min="0" value="0" required style="width: 100px; text-align: center;">
                        <button type="button" onclick="adjustEditStock(1)" style="width: 40px; height: 40px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-size: 20px;">+</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Image Path</label>
                    <input type="text" id="editProductImage" required>
                </div>

                <button type="submit" class="submit-btn">Update Product</button>
            </form>
        </div>
    </div>

<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Product</h2>
            <button class="close-modal" onclick="closeModal('addProductModal')">&times;</button>
        </div>
        <form id="addProductForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" id="addProductName" required>
            </div>

            <div class="form-group">
                <label>Price (₱)</label>
                <input type="number" id="addProductPrice" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="addProductDescription" rows="4" required></textarea>
            </div>

            <div class="form-group">
            <label>Stock Quantity</label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" onclick="adjustAddStock(-1)" style="width: 40px; height: 40px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-size: 20px;">−</button>
                <input type="number" id="addProductStock" min="0" value="0" required style="width: 100px; text-align: center;">
                <button type="button" onclick="adjustAddStock(1)" style="width: 40px; height: 40px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-size: 20px;">+</button>
            </div>
        </div>

            <div class="form-group">
                <label>Product Image</label>
                <input type="file" id="addProductImage" accept="image/*" required style="display: none;">
                <label for="addProductImage" class="upload-label">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/>
                    </svg>
                    <span id="uploadText">Choose Image</span>
                </label>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Accepted formats: JPG, JPEG, PNG, GIF (Max 5MB)
                </small>
                <div id="imagePreview" style="margin-top: 10px; display: none;">
                    <img id="previewImg" style="max-width: 200px; border-radius: 10px; border: 2px solid #e0e0e0;">
                </div>
            </div>

            <button type="submit" class="submit-btn">Add Product</button>
        </form>
    </div>
</div>

<!-- Add Product Success Modal -->
<div id="addSuccessModal" class="success-modal-overlay">
    <div class="success-modal-content">
        <button class="success-modal-close" onclick="closeAddSuccessModal()">&times;</button>
        <div class="success-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2 class="success-modal-title">Product Added!</h2>
        <p class="success-modal-text">The new product has been added successfully.</p>
        <button class="success-modal-btn" onclick="closeAddSuccessModal()">OK</button>
    </div>
</div>


 <!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="success-modal-content">
        <button class="close-modal" onclick="closeSuccessModal()">&times;</button>
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2>Order Status Updated!</h2>
        <p>The order status has been updated successfully.</p>
        <div class="success-modal-buttons">
            <button class="success-btn update-again" onclick="updateAnotherOrder()">Update Again</button>
            <button class="success-btn ok" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<!-- Edit Product Success Modal -->
<div id="editSuccessModal" class="success-modal-overlay">
    <div class="success-modal-content">
        <button class="success-modal-close" onclick="closeEditSuccessModal()">&times;</button>
        <div class="success-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2 class="success-modal-title">Product Updated!</h2>
        <p class="success-modal-text">The product has been updated successfully.</p>
        <button class="success-modal-btn" onclick="closeEditSuccessModalAndRedirect()">OK</button>
    </div>
</div>

<!-- Toggle Product Status Confirmation Modal -->
<div id="toggleModal" class="toggle-modal-overlay">
    <div class="toggle-modal-content">
        <button class="toggle-modal-close" onclick="closeToggleModal()">&times;</button>
        
        <div class="toggle-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
        </div>
        
        <h2 class="toggle-modal-title" id="toggleModalTitle">Deactivate Product?</h2>
        <p class="toggle-modal-text" id="toggleModalText">Are you sure you want to deactivate this product? It will be hidden from customers.</p>
        
        <div class="toggle-modal-buttons">
            <button class="toggle-modal-btn toggle-modal-btn-cancel" onclick="closeToggleModal()">Cancel</button>
            <button class="toggle-modal-btn toggle-modal-btn-confirm" onclick="confirmToggleProduct()" id="toggleConfirmBtn">Yes, Deactivate</button>
        </div>
    </div>
</div>

<!-- Toggle Success Modal -->
<div id="toggleSuccessModal" class="success-modal-overlay">
    <div class="success-modal-content">
        <button class="success-modal-close" onclick="closeToggleSuccessModal()">&times;</button>
        <div class="success-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2 class="success-modal-title" id="toggleSuccessTitle">Product Updated!</h2>
        <p class="success-modal-text" id="toggleSuccessText">The product status has been updated successfully.</p>
        <button class="success-modal-btn" onclick="closeToggleSuccessModal()">OK</button>
    </div>
</div>

               <!-- Logout Confirmation Modal -->
        <div id="logoutModal" class="logout-modal-overlay">
            <div class="logout-modal-content">
                <button class="logout-modal-close" onclick="closeLogoutModal()">&times;</button>
                
                <div class="logout-modal-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                </div>
                
                <h2 class="logout-modal-title">Logout?</h2>
                <p class="logout-modal-text">Are you sure you want to log out of your account?</p>
                
                <div class="logout-modal-buttons">
                    <button class="logout-modal-btn logout-modal-btn-cancel" onclick="closeLogoutModalAdmin()">Cancel</button>
                    <button class="logout-modal-btn logout-modal-btn-confirm" onclick="confirmLogoutAdmin()">Yes, Logout</button>
                </div>
            </div>
        </div>

        <!-- Delete Product Confirmation Modal -->
<div id="deleteProductModal" class="delete-modal-overlay">
    <div class="delete-modal-content">
        <button class="delete-modal-close" onclick="closeDeleteModal()">&times;</button>
        
        <div class="delete-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
            </svg>
        </div>
        
        <h2 class="delete-modal-title">Delete Product?</h2>
        <p class="delete-modal-text" id="deleteProductText">Are you sure you want to permanently delete this product?</p>
        
        <div class="delete-modal-buttons">
            <button class="delete-modal-btn delete-modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="delete-modal-btn delete-modal-btn-confirm" onclick="confirmDeleteProduct()">Yes, Delete</button>
        </div>
    </div>
</div>

<!-- Grant Admin Confirmation Modal -->
<div id="grantAdminModal" class="toggle-modal-overlay">
    <div class="toggle-modal-content">
        <button class="toggle-modal-close" onclick="closeGrantAdminModal()">&times;</button>
        
        <div class="toggle-modal-icon" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
            <svg viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>
        
        <h2 class="toggle-modal-title">Grant Admin Access?</h2>
        <p class="toggle-modal-text" id="grantAdminText">Are you sure you want to grant admin access to this user?</p>
        
        <div class="toggle-modal-buttons">
            <button class="toggle-modal-btn toggle-modal-btn-cancel" onclick="closeGrantAdminModal()">Cancel</button>
            <button class="toggle-modal-btn toggle-modal-btn-confirm" style="background-color: #bee5eb; border-color: #bee5eb; color: #6b4b50;" onclick="confirmGrantAdmin()">Yes</button>
        </div>
    </div>
</div>

<!-- Revoke Admin Confirmation Modal -->
<div id="revokeAdminModal" class="delete-modal-overlay">
    <div class="delete-modal-content">
        <button class="delete-modal-close" onclick="closeRevokeAdminModal()">&times;</button>
        
        <div class="delete-modal-icon" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#856404"/>
            </svg>
        </div>
        
        <h2 class="delete-modal-title">Revoke Admin Access?</h2>
        <p class="delete-modal-text" id="revokeAdminText">Are you sure you want to revoke admin access from this user?</p>
        
        <div class="delete-modal-buttons">
            <button class="delete-modal-btn delete-modal-btn-cancel" onclick="closeRevokeAdminModal()">Cancel</button>
            <button class="delete-modal-btn delete-modal-btn-confirm" style="background-color: #ffeaa7; border-color: #ffeaa7; color: #6b4b50;" onclick="confirmRevokeAdmin()">Yes</button>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div id="deleteUserModal" class="delete-modal-overlay">
    <div class="delete-modal-content">
        <button class="delete-modal-close" onclick="closeDeleteUserModal()">&times;</button>
        
        <div class="delete-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
            </svg>
        </div>
        
        <h2 class="delete-modal-title">Delete User?</h2>
        <p class="delete-modal-text" id="deleteUserText">Are you sure you want to permanently delete this user? This will also delete all their orders and order items. This action cannot be undone.</p>
        
        <div class="delete-modal-buttons">
            <button class="delete-modal-btn delete-modal-btn-cancel" onclick="closeDeleteUserModal()">Cancel</button>
            <button class="delete-modal-btn delete-modal-btn-confirm" onclick="confirmDeleteUser()">Yes, Delete</button>
        </div>
    </div>
</div>

<!-- User Action Success Modal -->
<div id="userSuccessModal" class="success-modal-overlay">
    <div class="success-modal-content">
        <button class="success-modal-close" onclick="closeUserSuccessModal()">&times;</button>
        <div class="success-modal-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2 class="success-modal-title" id="userSuccessTitle">Success!</h2>
        <p class="success-modal-text" id="userSuccessText">The action was completed successfully.</p>
        <button class="success-modal-btn" onclick="closeUserSuccessModal()">OK</button>
    </div>
</div>

<script>
    let lastUpdatedOrderId = null;

    // Date Filter Functions
    function applyDateFilter() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date cannot be after end date');
            return;
        }

        // Redirect with date parameters
        window.location.href = `admin.php?start_date=${startDate}&end_date=${endDate}`;
    }

    function resetDateFilter() {
        // Redirect to admin.php without parameters (uses PHP defaults)
        window.location.href = 'admin.php';
    }

    // Tab Switching
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    // Filter Orders by Status
    let currentFilter = 'all';
    function filterByStatus(status) {
        currentFilter = status;
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        const rows = document.querySelectorAll('.order-row');
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Search Orders
    function filterOrders() {
        const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.order-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchesSearch = text.includes(searchTerm);
            const matchesFilter = currentFilter === 'all' || row.dataset.status === currentFilter;
            
            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Search Users
    function filterUsers() {
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Update Order Status
    function updateOrderStatus(orderId) {
        lastUpdatedOrderId = orderId;
        document.getElementById('statusOrderId').value = orderId;
        document.getElementById('displayOrderId').value = '#' + orderId;
        document.getElementById('statusModal').classList.add('active');
    }

    // View Order Details
    function viewOrder(orderId) {
        fetch('admin_actions.php?action=get_order&order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                const items = data.items;

                let html = `
                    <div class="order-items">
                        <h4>Order Items:</h4>
                `;

                items.forEach(item => {
                    html += `
                        <div class="order-item">
                            <span>${item.product_name} x${item.quantity}</span>
                            <span>₱${parseFloat(item.price).toFixed(2)}</span>
                        </div>
                    `;
                });

                html += `</div><br>`;

                html += `
                    <p><strong>Order ID:</strong> #${order.id}</p>
                    <p><strong>Customer:</strong> ${order.fullname}</p>
                    <p><strong>Email:</strong> ${order.email}</p>
                    <p><strong>Phone:</strong> ${order.phone}</p>
                    <p><strong>Address:</strong> ${order.delivery_address}</p>
                    <p><strong>Payment Method:</strong> ${order.payment_method.toUpperCase()}</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-${order.status}">
                            ${order.status}
                        </span>
                    </p>
                    <p><strong>Total:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
                    <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                `;

                if (order.payment_proof) {
                    html += `
                        <div style="margin-top: 20px;">
                            <p><strong>Payment Proof:</strong></p>
                            <img 
                                src="${order.payment_proof}" 
                                alt="Payment Proof" 
                                style="max-width: 100%; max-height: 400px; border-radius: 10px; margin-top: 10px;"
                            >
                        </div>
                    `;
                }

                document.getElementById('orderDetails').innerHTML = html;
                document.getElementById('viewOrderModal').classList.add('active');
            }
        });
    }

    function toggleDateFilter() {
        document.getElementById('dateRangeFilter').classList.toggle('open');
    }

    // Close Modal
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    // Add Product Function
    function addProduct() {
        document.getElementById('addProductModal').classList.add('active');
    }

    // Close Add Success Modal
    function closeAddSuccessModal() {
        document.getElementById('addSuccessModal').classList.remove('active');
        sessionStorage.setItem('activeTab', 'products');
        location.reload();
    }

    // Edit Product
    function editProduct(productId) {
        fetch('admin_actions.php?action=get_product&product_id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                
                document.getElementById('editProductId').value = product.id;
                document.getElementById('editProductName').value = product.name;
                document.getElementById('editProductPrice').value = product.price;
                document.getElementById('editProductDescription').value = product.description;
                document.getElementById('editProductStock').value = product.stock || 0;
                document.getElementById('editProductImage').value = product.image_path;
                
                document.getElementById('editProductModal').classList.add('active');
            } else {
                alert('Error loading product: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading product');
        });
    }

    // Toggle Product Status
    let toggleProductId = null;
    let toggleCurrentStatus = null;

    function toggleProductStatus(productId, currentStatus) {
        toggleProductId = productId;
        toggleCurrentStatus = parseInt(currentStatus);
        
        const modal = document.getElementById('toggleModal');
        const title = document.getElementById('toggleModalTitle');
        const text = document.getElementById('toggleModalText');
        const confirmBtn = document.getElementById('toggleConfirmBtn');
        
        if (toggleCurrentStatus === 1) {
            title.textContent = 'Deactivate Product?';
            text.textContent = 'Are you sure you want to deactivate this product? It will be hidden from customers.';
            confirmBtn.textContent = 'Yes, Deactivate';
        } else {
            title.textContent = 'Activate Product?';
            text.textContent = 'Are you sure you want to activate this product? It will be visible to customers.';
            confirmBtn.textContent = 'Yes, Activate';
        }
        
        modal.classList.add('active');
    }

    function closeToggleModal() {
        document.getElementById('toggleModal').classList.remove('active');
        toggleProductId = null;
        toggleCurrentStatus = null;
    }

    function confirmToggleProduct() {
        if (toggleProductId === null) return;
        
        const formData = new FormData();
        formData.append('action', 'toggle_product');
        formData.append('id', toggleProductId);
        formData.append('is_active', toggleCurrentStatus);
        
        fetch('admin_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            closeToggleModal();
            
            if (data.success) {
                sessionStorage.setItem('activeTab', 'products');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeToggleModal();
            alert('Error updating product status');
        });
    }

    // Delete Product
    let deleteProductId = null;

    function deleteProduct(productId, productName) {
        deleteProductId = productId;
        document.getElementById('deleteProductText').textContent = 
            `Are you sure you want to permanently delete "${productName}"? This action cannot be undone.`;
        document.getElementById('deleteProductModal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteProductModal').classList.remove('active');
        deleteProductId = null;
    }

    function confirmDeleteProduct() {
        if (deleteProductId === null) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_product');
        formData.append('id', deleteProductId);
        
        fetch('admin_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            closeDeleteModal();
            
            if (data.success) {
                sessionStorage.setItem('activeTab', 'products');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeDeleteModal();
            alert('Error deleting product');
        });
    }

    // Success Modal Functions
    function closeSuccessModal() {
        document.getElementById('successModal').classList.remove('active');
        location.reload();
    }

    function updateAnotherOrder() {
        document.getElementById('successModal').classList.remove('active');
        if (lastUpdatedOrderId) {
            updateOrderStatus(lastUpdatedOrderId);
        }
    }

    // Logout Functions
    function openLogoutModal() {
        document.getElementById('logoutModal').classList.add('active');
    }

    function closeLogoutModalAdmin() {
        document.getElementById('logoutModal').classList.remove('active');
    }

    function confirmLogoutAdmin() {
        window.location.href = 'admin_logout.php';
    }

    // Edit Success Modal Functions
    function closeEditSuccessModal() {
        document.getElementById('editSuccessModal').classList.remove('active');
    }

    function closeEditSuccessModalAndRedirect() {
        closeEditSuccessModal();
        sessionStorage.setItem('activeTab', 'products');
        location.reload();
    }

    // Stock Adjustment Functions
    function adjustEditStock(amount) {
        const input = document.getElementById('editProductStock');
        const currentValue = parseInt(input.value) || 0;
        const newValue = Math.max(0, currentValue + amount);
        input.value = newValue;
    }

    function adjustAddStock(amount) {
        const input = document.getElementById('addProductStock');
        const currentValue = parseInt(input.value) || 0;
        const newValue = Math.max(0, currentValue + amount);
        input.value = newValue;
    }

    // DOMContentLoaded Event - ALL EVENT LISTENERS GO HERE
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we need to switch to a specific tab on page load
        const activeTab = sessionStorage.getItem('activeTab');
        if (activeTab) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(activeTab + '-tab').classList.add('active');
            
            const tabButtons = document.querySelectorAll('.tab-btn');
            if (activeTab === 'orders') tabButtons[0].classList.add('active');
            if (activeTab === 'users') tabButtons[1].classList.add('active');
            if (activeTab === 'products') tabButtons[2].classList.add('active');
            
            sessionStorage.removeItem('activeTab');
        }

        // Status Form Submit
        const statusForm = document.getElementById('statusForm');
        if (statusForm) {
            statusForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const orderId = document.getElementById('statusOrderId').value;
                const newStatus = document.getElementById('newStatus').value;
                
                const formData = new FormData();
                formData.append('action', 'update_status');
                formData.append('order_id', orderId);
                formData.append('status', newStatus);
                
                fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal('statusModal');
                        document.getElementById('successModal').classList.add('active');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating status');
                });
            });
        }

        // Add Product Image Preview
        const addProductImage = document.getElementById('addProductImage');
        if (addProductImage) {
            addProductImage.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    document.getElementById('uploadText').textContent = file.name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Add Product Form Submit
        const addProductForm = document.getElementById('addProductForm');
        if (addProductForm) {
            addProductForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const fileInput = document.getElementById('addProductImage');
                const file = fileInput.files[0];
                
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, JPEG, PNG, and GIF images are allowed');
                    return;
                }
                
                const formData = new FormData();
                formData.append('action', 'add_product');
                formData.append('name', document.getElementById('addProductName').value);
                formData.append('price', document.getElementById('addProductPrice').value);
                formData.append('description', document.getElementById('addProductDescription').value);
                formData.append('stock', document.getElementById('addProductStock').value);
                formData.append('product_image', file);
                
                const submitBtn = e.target.querySelector('.submit-btn');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Uploading...';
                submitBtn.disabled = true;
                
                fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    
                    if (data.success) {
                        closeModal('addProductModal');
                        document.getElementById('addProductForm').reset();
                        document.getElementById('imagePreview').style.display = 'none';
                        document.getElementById('addSuccessModal').classList.add('active');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    alert('Error adding product');
                });
            });
        }

        // Edit Product Form Submit
        const editProductForm = document.getElementById('editProductForm');
        if (editProductForm) {
            editProductForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                formData.append('action', 'update_product');
                formData.append('id', document.getElementById('editProductId').value);
                formData.append('name', document.getElementById('editProductName').value);
                formData.append('price', document.getElementById('editProductPrice').value);
                formData.append('description', document.getElementById('editProductDescription').value);
                formData.append('stock', document.getElementById('editProductStock').value);
                formData.append('image_path', document.getElementById('editProductImage').value);
                
                fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal('editProductModal');
                        document.getElementById('editSuccessModal').classList.add('active');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating product');
                });
            });
        }

 // Close modal on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal') || 
        event.target.classList.contains('success-modal-overlay') ||
        event.target.classList.contains('logout-modal-overlay') ||
        event.target.classList.contains('toggle-modal-overlay') ||
        event.target.classList.contains('delete-modal-overlay')) {
        event.target.classList.remove('active');
    }
};
    });

    // Toggle Date Filter
    function toggleDateFilter() {
        document.getElementById('dateRangeFilter').classList.toggle('open');
    }

    // Close date filter when clicking outside
    document.addEventListener('click', function(event) {
        const dateFilter = document.getElementById('dateRangeFilter');
        const isClickInside = dateFilter.contains(event.target);
        
        if (!isClickInside && dateFilter.classList.contains('open')) {
            dateFilter.classList.remove('open');
        }
    });

    // Close date filter when pressing ESC key
    document.addEventListener('keydown', function(event) {
        const dateFilter = document.getElementById('dateRangeFilter');
        
        if (event.key === 'Escape' && dateFilter.classList.contains('open')) {
            dateFilter.classList.remove('open');
        }
    });

    // ==========================================
    // USER MANAGEMENT FUNCTIONS
    // ==========================================
    
    // Delete User
    function deleteUser(userId, userName) {
        if (!confirm(`Are you sure you want to delete user "${userName}"?\n\nWARNING: This will also delete:\n• All orders placed by this user\n• All order items\n\nThis action cannot be undone!`)) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('user_id', userId);
        
        fetch('admin_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✓ ' + data.message);
                
                // Remove the row from the table
                const allRows = document.querySelectorAll('tr.user-row');
                allRows.forEach(row => {
                    if (row.cells[0].textContent == userId) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                });
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting user. Please try again.');
        });
    }

let currentUserId = null;
let currentUserName = null;
let currentAdminStatus = null;

// Toggle Admin Status - Opens Modal
function toggleAdminStatus(userId, currentStatus) {
    currentUserId = userId;
    currentAdminStatus = currentStatus;
    
    // Get user name from the table
    const allRows = document.querySelectorAll('tr.user-row');
    allRows.forEach(row => {
        if (row.cells[0].textContent == userId) {
            currentUserName = row.cells[1].textContent;
        }
    });
    
    if (currentStatus == 1) {
        // Revoke admin
        document.getElementById('revokeAdminText').textContent = 
            `Are you sure you want to revoke admin access from "${currentUserName}"?`;
        document.getElementById('revokeAdminModal').classList.add('active');
    } else {
        // Grant admin
        document.getElementById('grantAdminText').textContent = 
            `Are you sure you want to grant admin access to "${currentUserName}"?`;
        document.getElementById('grantAdminModal').classList.add('active');
    }
}

function closeGrantAdminModal() {
    document.getElementById('grantAdminModal').classList.remove('active');
    currentUserId = null;
    currentUserName = null;
    currentAdminStatus = null;
}

function closeRevokeAdminModal() {
    document.getElementById('revokeAdminModal').classList.remove('active');
    currentUserId = null;
    currentUserName = null;
    currentAdminStatus = null;
}

function confirmGrantAdmin() {
    performAdminToggle();
}

function confirmRevokeAdmin() {
    performAdminToggle();
}

function performAdminToggle() {
    if (currentUserId === null) return;
    
    const formData = new FormData();
    formData.append('action', 'toggle_admin');
    formData.append('user_id', currentUserId);
    formData.append('is_admin', currentAdminStatus);
    
    fetch('admin_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        closeGrantAdminModal();
        closeRevokeAdminModal();
        
        if (data.success) {
            // Set active tab to users before reload
            sessionStorage.setItem('activeTab', 'users');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        closeGrantAdminModal();
        closeRevokeAdminModal();
        alert('Error updating admin status. Please try again.');
    });
}

// Delete User - Opens Modal
function deleteUser(userId, userName) {
    currentUserId = userId;
    currentUserName = userName;
    
    document.getElementById('deleteUserText').textContent = 
        `Are you sure you want to permanently delete "${userName}"? This will also delete all their orders and order items. This action cannot be undone.`;
    document.getElementById('deleteUserModal').classList.add('active');
}

function closeDeleteUserModal() {
    document.getElementById('deleteUserModal').classList.remove('active');
    currentUserId = null;
    currentUserName = null;
}

function confirmDeleteUser() {
    if (currentUserId === null) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('user_id', currentUserId);
    
    fetch('admin_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        closeDeleteUserModal();
        
        if (data.success) {
            // Set active tab to users before reload
            sessionStorage.setItem('activeTab', 'users');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        closeDeleteUserModal();
        alert('Error deleting user. Please try again.');
    });
}

// DELETE THIS FUNCTION - No longer needed
function closeUserSuccessModal() {
    document.getElementById('userSuccessModal').classList.remove('active');
    currentUserId = null;
    currentUserName = null;
    currentAdminStatus = null;
}

</script>
</body>
</html>