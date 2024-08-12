<?php
session_start();
include 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

$counselor_id = $_SESSION['counselor_id'];

// Handle form submission for creating a new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name']) && isset($_POST['category_description'])) {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    // Check if the category already exists
    $sql = "SELECT id FROM categories WHERE counselor_id = ? AND category_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $counselor_id, $category_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Error: Category with this name already exists!";
        $_SESSION['message_type'] = 'error';
    } else {
        // Insert new category
        $sql = "INSERT INTO categories (counselor_id, category_name, category_description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $counselor_id, $category_name, $category_description);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Category created successfully!";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
    }
}

// Handle update and delete requests
if (isset($_GET['action'])) {
    $category_id = $_GET['id'] ?? null;

    if ($_GET['action'] == 'delete' && $category_id) {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $category_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Category deleted successfully!";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
    } elseif ($_GET['action'] == 'edit' && $category_id) {
        // Redirect to edit page
        header("Location: edit_category.php?id=$category_id");
        exit;
    }
}

// Retrieve all categories created by the counselor
$sql = "SELECT * FROM categories WHERE counselor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $counselor_id);
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Category</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
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
        .dashboard-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 800px;
            padding: 20px;
            transition: transform 0.3s; /* Smooth transition for margin */
            margin-top: 60px; /* Adjust based on navbar height */
        }
        h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            width: 100%;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: skyblue;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .message {
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            width: 100%;
            text-align: center;
        }
        .message.success {
            background-color: #28a745; /* Green for success */
        }
        .message.error {
            background-color: #dc3545; /* Red for error */
        }
        .table-container {
            width: 100%;
            max-width: 1000px; /* Adjust as needed */
            max-height: 400px; /* Fixed height for the table container */
            overflow-y: auto; /* Vertical scrolling */
            overflow-x: hidden; /* Hide horizontal scrolling */
        }
        .category-table {
            width: 100%;
            border-collapse: collapse;
        }
        .category-table th, .category-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .category-table th {
            background-color: #007bff;
            color: #fff;
            text-align: left;
        }
        .category-table tr:nth-child(even) {
            background-color: skyblue;
        }
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            text-decoration: none;
            color: #fff;
            border-radius: 3px;
            font-size: 14px;
        }
        .btn-edit {
            background-color: #007bff;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
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
            .dashboard-container {
                transform: translateX(0); /* Reset transform when menu is hidden */
            }
        }
        @media screen and (min-width: 769px) {
            .slide-menu.show {
                left: 0; /* Show menu on large screens */
            }
            .dashboard-container.with-menu {
                transform: translateX(125px); /* Center content when menu is shown */
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

<div class="dashboard-container" id="container">
    <?php
        if (isset($_SESSION['message'])) {
            $message_type = $_SESSION['message_type'] ?? 'success';
            echo '<div class="message ' . htmlspecialchars($message_type) . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']); // Clear the message after displaying it
        }
    ?>
    <h2>Create a New Category</h2>
    <form action="CreateCategory.php" method="POST">
        <div class="form-group">
            <label for="category_name">Category Name</label>
            <input type="text" id="category_name" name="category_name" required>
        </div>
        <div class="form-group">
            <label for="category_description">Category Description</label>
            <textarea id="category_description" name="category_description" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <button type="submit">Create Category</button>
        </div>
    </form>

    <h2>Existing Categories</h2>
    <div class="table-container">
        <table class="category-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($categories as $category) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($category['category_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($category['category_description']) . '</td>';
                        echo '<td>' . htmlspecialchars($category['created_at']) . '</td>';
                        echo '<td>';
                        echo '<a href="?action=edit&id=' . htmlspecialchars($category['id']) . '" class="btn-edit">Edit</a> ';
                        echo '<a href="?action=delete&id=' . htmlspecialchars($category['id']) . '" class="btn-delete" onclick="return confirm(\'Are you sure you want to delete this category?\')">Delete</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
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
            } else {
                container.classList.remove('with-menu');
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
