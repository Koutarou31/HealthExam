<?php
session_start();
include 'config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['new_username'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $counselor_id = $_SESSION['counselor_id'];

    $sql = "UPDATE counselors SET username = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $new_username, $new_password, $counselor_id);

    if ($stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        /* Similar styles as the login page */
    </style>
</head>
<body>

<div class="login-container">
    <h2>Update Profile</h2>
    <form action="update_profile.php" method="POST">
        <div class="input-group">
            <label for="new_username">New Username</label>
            <input type="text" id="new_username" name="new_username" required>
        </div>
        <div class="input-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="input-group">
            <button type="submit">Update</button>
        </div>
    </form>
</div>

</body>
</html>