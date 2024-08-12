<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

include 'config.php';

$counselor_id = $_SESSION['counselor_id'];
$exam_id = $_GET['id'] ?? null;

// Fetch the exam details
if ($exam_id) {
    $sql = "SELECT * FROM exams WHERE id = ? AND counselor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $exam_id, $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exam = $result->fetch_assoc();
    if (!$exam) {
        $_SESSION['message'] = "Exam not found or you do not have permission to edit it.";
        header('Location: CreateExam.php');
        exit;
    }
} else {
    $_SESSION['message'] = "Invalid exam ID.";
    header('Location: CreateExam.php');
    exit;
}

// Handle form submission for updating the exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['exam_name']) && isset($_POST['exam_description'])) {
    $exam_name = $_POST['exam_name'];
    $exam_description = $_POST['exam_description'];

    $sql = "UPDATE exams SET exam_name = ?, exam_description = ? WHERE id = ? AND counselor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssii', $exam_name, $exam_description, $exam_id, $counselor_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Exam updated successfully!";
        header('Location: CreateExam.php');
        exit;
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam</title>
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
            background-color: #007bff;
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
        .form-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
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
            background-color: #007bff;
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
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="CreateExam.php">Back to Exams</a>
</div>

<div class="form-container">
    <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']); // Clear the message after displaying it
        }
    ?>
    <h2>Edit Exam</h2>
    <form action="edit_exam.php?id=<?php echo htmlspecialchars($exam_id); ?>" method="POST">
        <div class="form-group">
            <label for="exam_name">Exam Name</label>
            <input type="text" id="exam_name" name="exam_name" value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="exam_description">Exam Description</label>
            <textarea id="exam_description" name="exam_description" rows="4" required><?php echo htmlspecialchars($exam['exam_description']); ?></textarea>
        </div>
        <div class="form-group">
            <button type="submit">Update Exam</button>
        </div>
    </form>
</div>

</body>
</html>
