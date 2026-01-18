<?php
// db_connect.php
$servername = "localhost";
$username = "your username";
$password = "your password";
$dbname = "chibi_bites";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If it's an AJAX request, return JSON, otherwise just die
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}
?>