<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .navbar {
            background-color: skyblue;
            color: #fff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
            
        }
        .navbar a:hover {
            background-color: #0056b3;
        }
        .navbar img {
            height: 40px; /* Resize the image to a desired height */
            width: auto; /* Maintain aspect ratio */
            margin-right: 15px; /* Space between image and text */
            border-radius:10px;
            margin-left: 20px;
            
        }
        .container {
            display: flex;
            justify-content: center;
            width: 100%;
            max-width: 800px;
            margin-top: 80px; /* Adjust based on navbar height */
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .input-group button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .login-button {
            background-color: skyblue;
        }
        .login-button:hover {
            background-color: pink;
        }
        .register-button {
            background-color: #007bff;
        }
        .register-button:hover {
            background-color: #0056b3;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>
        
        <a href="index.php">StudentHealth</a>
    </div>
    <div>
        <img href="index.php" src="https://img-s-msn-com.akamaized.net/tenant/amp/entityid/BB1msMCg.img" alt="Student Health Image">
        
    </div>
</div>

<div class="container">
    <div class="form-container">
        <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="message">' . $_SESSION['message'] . '</div>';
                unset($_SESSION['message']); // Clear the message after displaying it
            }
        ?>
        <h2>Guidance Login</h2>
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <button type="submit" class="login-button">Login</button>
            </div>
            <div>
                <a href = "signin.php">Sign-in</a>
          
            </div>
        </form>
    </div>
</div>

</body>
</html>