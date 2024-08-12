<?php
$host = '127.0.1.2';
$db = 'health_exams';
$user = 'root';
$pass = '1234';
$port = 3307;

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>