<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $status = $_POST['status'];
    $location = trim($_POST['location']);
    $item_date = !empty($_POST['item_date']) ? $_POST['item_date'] : null;

    if (empty($title) || empty($category) || empty($status)) {
        $error = "Please fill in all required fields.";
    } else {
        $image_stream = null;
        $image_mime = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
                $error = "Image upload failed. Please try a smaller file (max 2 MB).";
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = "Image is too large. Maximum size is 2 MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['image']['tmp_name']);
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (in_array($mime, $allowed, true)) {
                    $image_stream = fopen($_FILES['image']['tmp_name'], 'rb');
                    $image_mime = $mime;
                } else {
                    $error = "Invalid image type. Only JPG, PNG, GIF, WEBP allowed.";
                }
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, category, status, location, item_date, image_data, image_mime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $_SESSION['user_id']);
            $stmt->bindValue(2, $title);
            $stmt->bindValue(3, $description);
            $stmt->bindValue(4, $category);
            $stmt->bindValue(5, $status);
            $stmt->bindValue(6, $location);
            $stmt->bindValue(7, $item_date);
            if ($image_stream !== null) {
                $stmt->bindValue(8, $image_stream, PDO::PARAM_LOB);
            } else {
                $stmt->bindValue(8, null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(9, $image_mime);
            $stmt->execute();

            header("Location: dashboard.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post Item - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Lost & Found</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="index.php">Browse All Items</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="form-container">
        <h2>Post an Item</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="add_item.php" enctype="multipart/form-data">
            <label>Title</label>
            <input type="text" name="title" required>

            <label>Description</label>
            <textarea name="description" rows="4"></textarea>

            <label>Category</label>
            <select name="category" required>
                <option value="Electronics">Electronics</option>
                <option value="Documents">Documents</option>
                <option value="Bags">Bags</option>
                <option value="Clothing">Clothing</option>
                <option value="Accessories">Accessories</option>
                <option value="Other">Other</option>
            </select>

            <label>Status</label>
            <select name="status" required>
                <option value="lost">Lost</option>
                <option value="found">Found</option>
            </select>

            <label>Location</label>
            <input type="text" name="location" placeholder="e.g. Library, 2nd floor">

            <label>Date</label>
            <input type="date" name="item_date">

            <label>Photo (optional)</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Post Item</button>
        </form>
    </div>
    <script src="app.js"></script>
</body>
</html>