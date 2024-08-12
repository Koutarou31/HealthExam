<?php
// Start session (if needed)
session_start();

// Display errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include your database connection file
include 'config.php';

// Define the username and the new password you want to hash and update
$username = 'james'; // Replace with the actual username you want to update
$new_password = '123'; // Replace with the actual password you want to hash and update

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Prepare the SQL statement to update the password
$sql = "UPDATE counselors SET password = ? WHERE username = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}

// Bind the parameters
$stmt->bind_param('ss', $hashed_password, $username);

// Execute the query
if ($stmt->execute()) {
    echo "Password updated successfully for user '$username'.";
} else {
    echo "Error updating password: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>