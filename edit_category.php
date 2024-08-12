<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

include 'config.php';

$counselor_id = $_SESSION['counselor_id'];
$category_id = $_GET['id'] ?? null;

// Fetch the category details
if ($category_id) {
    $sql = "SELECT * FROM categories WHERE id = ? AND counselor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $category_id, $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    if (!$category) {
        $_SESSION['message'] = "Category not found or you do not have permission to edit it.";
        header('Location: CreateCategory.php');
        exit;
    }
} else {
    $_SESSION['message'] = "Invalid category ID.";
    header('Location: CreateCategory.php');
    exit;
}

// Handle form submission for updating the category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name']) && isset($_POST['category_description'])) {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    $sql = "UPDATE categories SET category_name = ?, category_description = ? WHERE id = ? AND counselor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssii', $category_name, $category_description, $category_id, $counselor_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Category updated successfully!";
        header('Location: CreateCategory.php');
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
    <title>Edit Category</title>
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
    <a href="CreateCategory.php">Back to Categories</a>
</div>

<div class="form-container">
    <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']); // Clear the message after displaying it
        }
    ?>
    <h2>Edit Category</h2>
    <form action="edit_category.php?id=<?php echo htmlspecialchars($category_id); ?>" method="POST">
        <div class="form-group">
            <label for="category_name">Category Name</label>
            <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="category_description">Category Description</label>
            <textarea id="category_description" name="category_description" rows="4" required><?php echo htmlspecialchars($category['category_description']); ?></textarea>
        </div>
        <div class="form-group">
            <button type="submit">Update Category</button>
        </div>
    </form>
</div>

</body>
</html>
