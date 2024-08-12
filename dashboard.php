<?php
session_start();

// Cache control headers to prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

// Handle log out
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Prevent horizontal scroll */
            display: flex;
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
        .burger-icon {
            font-size: 24px;
            cursor: pointer;
            display: none; /* Hidden by default */
        }
        .slide-menu {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px; /* Hidden off-screen by default */
            background-color: skyblue;
            color: #fff;
            overflow-x: hidden;
            padding-top: 60px;
            z-index: 999;
            transition: left 0.3s; /* Smooth transition for sliding effect */
        }
        .slide-menu a {
            color: #fff;
            text-decoration: none;
            padding: 15px 25px;
            display: block;
            transition: background-color 0.3s;
        }
        .slide-menu a:hover {
            background-color: #0056b3;
        }
        .container {
            padding: 20px;
            margin-top: 60px; /* Adjust based on navbar height */
            flex-grow: 1; /* Allow the container to take up the remaining space */
            display: flex;
            flex-direction: column;
            align-items: center; /* Center content horizontally */
            justify-content: center; /* Center content vertically */
            height: calc(100vh - 60px); /* Full viewport height minus navbar height */
            transition: margin-left 0.3s; /* Smooth transition for margin */
        }
        .message-wrapper {
            display: flex;
            justify-content: center; /* Center horizontally */
            margin-bottom: 15px;
        }
        .message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            width: 100%;
            max-width: 600px; /* Adjust max-width as needed */
            text-align: center; /* Center text inside the message box */
        }
        @media screen and (max-width: 768px) {
            .burger-icon {
                display: block; /* Show burger icon on small screens */
            }
            .navbar a.toggle-dashboard {
                display: none; /* Hide the Dashboard link on small screens */
            }
            .slide-menu.show {
                left: 0; /* Show menu when toggled */
            }
            .container {
                margin-left: 0; /* Reset margin when menu is hidden */
            }
        }
        @media screen and (min-width: 769px) {
            .slide-menu.show {
                left: 0; /* Show menu on large screens */
            }
            .container.with-menu {
                margin-left: 250px; /* Adjust content margin when menu is shown */
            }
        }
    </style>
</head>
<body>

<div class="slide-menu" id="slideMenu">
    <a href="CreateExam.php">Title</a>
    <a href="CreateCategory.php">Category</a>
    <a href="CreateQuestion.php">Question</a>
    <a href="#">Exam</a>
    <a href="AddStudent.php">Student</a>
    <a href="#">Assigning</a>
    <a href="#">Setting</a>
    <a href="dashboard.php?logout=true">Log Out</a>
</div>

<div class="navbar">
    <div class="burger-icon" onclick="toggleMenu()">&#9776;</div>
    <div>
        <a href="#" class="toggle-dashboard" onclick="toggleDashboard()">Dashboard</a>
    </div>
   
</div>

<div class="container" id="container">
    <div>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Use the menu on the left to manage exams, categories, questions, and students.</p>
    </div>
    <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message-wrapper"><div class="message">' . $_SESSION['message'] . '</div></div>';
            unset($_SESSION['message']); // Clear the message after displaying it
        }
    ?>
</div>

<script>
    function toggleMenu() {
        var menu = document.getElementById('slideMenu');
        var container = document.getElementById('container');
        var isVisible = menu.style.left === '0px';

        if (isVisible) {
            menu.style.left = '-250px';
            container.classList.remove('with-menu');
        } else {
            menu.style.left = '0px';
            container.classList.add('with-menu');
        }
    }

    function toggleDashboard() {
        if (window.innerWidth > 768) {
            toggleMenu();
        }
    }

    // Adjust menu behavior based on screen size
    window.addEventListener('resize', function() {
        var menu = document.getElementById('slideMenu');
        var container = document.getElementById('container');

        if (window.innerWidth > 768) {
            if (menu.style.left === '0px') {
                container.classList.add('with-menu');
            }
        } else {
            menu.style.left = '-250px';
            container.classList.remove('with-menu');
        }
    });

    // Ensure the menu is hidden on page load for larger screens
    document.addEventListener("DOMContentLoaded", function() {
        var menu = document.getElementById('slideMenu');
        var container = document.getElementById('container');

        if (window.innerWidth > 768) {
            menu.style.left = '-250px';
            container.classList.remove('with-menu');
        }
    });
</script>

</body>
</html>
