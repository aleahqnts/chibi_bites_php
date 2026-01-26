<?php
// InfinityFree Database Connection
$servername = "sql213.infinityfree.com";
$username = "if0_40993322";
$password = "iEBMGAqxXt9pYwn";
$database = "if0_40993322_chibi_bites_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to handle special characters
$conn->set_charset("utf8mb4");
?>