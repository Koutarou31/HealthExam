<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

include 'config.php';

$counselor_id = $_SESSION['counselor_id'];

// Handle form submission for creating a new exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['exam_name']) && isset($_POST['exam_description'])) {
    $exam_name = $_POST['exam_name'];
    $exam_description = $_POST['exam_description'];

    // Check if an exam with the same name already exists
    $sql = "SELECT id FROM exams WHERE exam_name = ? AND counselor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $exam_name, $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = [
            'text' => "An exam with this title already exists.",
            'type' => 'error'
        ];
    } else {
        // Insert new exam if title does not exist
        $sql = "INSERT INTO exams (counselor_id, exam_name, exam_description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $counselor_id, $exam_name, $exam_description);

        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'text' => "Exam created successfully!",
                'type' => 'success'
            ];
        } else {
            $_SESSION['message'] = [
                'text' => "Error: " . $stmt->error,
                'type' => 'error'
            ];
        }
    }
}

// Handle update and delete requests
if (isset($_GET['action'])) {
    $exam_id = $_GET['id'] ?? null;

    if ($_GET['action'] == 'delete' && $exam_id) {
        $sql = "DELETE FROM exams WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $exam_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'text' => "Exam deleted successfully!",
                'type' => 'success'
            ];
        } else {
            $_SESSION['message'] = [
                'text' => "Error: " . $stmt->error,
                'type' => 'error'
            ];
        }
    } elseif ($_GET['action'] == 'edit' && $exam_id) {
        // Redirect to edit page
        header("Location: edit_exam.php?id=$exam_id");
        exit;
    }
}

// Retrieve all exams created by the counselor
$sql = "SELECT * FROM exams WHERE counselor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $counselor_id);
$stmt->execute();
$result = $stmt->get_result();
$exams = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam</title>
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
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            width: 100%;
            text-align: center;
        }
        .message.success {
            background-color: #28a745; /* Green for success */
            color: white;
        }
        .message.error {
            background-color: #dc3545; /* Red for errors */
            color: white;
        }
        .table-container {
            width: 100%;
            max-width: 1000px; /* Adjust as needed */
            max-height: 400px; /* Fixed height for the table container */
            overflow-y: auto; /* Vertical scrolling */
            overflow-x: hidden; /* Hide horizontal scrolling */
        }
        .exam-table {
            width: 100%;
            border-collapse: collapse;
        }
        .exam-table th, .exam-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .exam-table th {
            background-color: #007bff;
            color: #fff;
            text-align: left;
        }
        .exam-table tr:nth-child(even) {
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
            $message = $_SESSION['message'];
            $messageClass = $message['type'] == 'error' ? 'error' : 'success';
            echo '<div class="message ' . $messageClass . '">' . $message['text'] . '</div>';
            unset($_SESSION['message']); // Clear the message after displaying it
        }
    ?>
    <h2>Create a Title</h2>
    <form action="CreateExam.php" method="POST">
        <div class="form-group">
            <label for="exam_name">Exam Name</label>
            <input type="text" id="exam_name" name="exam_name" required>
        </div>
        <div class="form-group">
            <label for="exam_description">Exam Description</label>
            <textarea id="exam_description" name="exam_description" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <button type="submit">Create Title</button>
        </div>
    </form>

    <h2>Existing Title</h2>
    <div class="table-container">
        <table class="exam-table">
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
                    foreach ($exams as $exam) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($exam['exam_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($exam['exam_description']) . '</td>';
                        echo '<td>' . htmlspecialchars($exam['created_at']) . '</td>';
                        echo '<td>';
                        echo '<a href="edit_exam.php?action=edit&id=' . htmlspecialchars($exam['id']) . '" class="btn-edit">Edit</a> ';
                        echo '<a href="?action=delete&id=' . htmlspecialchars($exam['id']) . '" class="btn-delete" onclick="return confirm(\'Are you sure you want to delete this exam?\')">Delete</a>';
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
