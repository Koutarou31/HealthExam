<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL query
    $sql = "INSERT INTO counselors (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $hashed_password);

    if ($stmt->execute()) {
        // Set a success message in the session
        $_SESSION['message'] = "Counselor registered successfully!";
        header('Location: index.php'); // Redirect to the main page or login page
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>