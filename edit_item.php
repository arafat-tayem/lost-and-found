<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = "";

// Fetch the item (without the heavy photo bytes), only if it belongs to this user
$stmt = $pdo->prepare("SELECT id, title, description, category, status, location, item_date, (image_data IS NOT NULL) AS has_image FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $status = $_POST['status'];
    $location = trim($_POST['location']);
    $item_date = !empty($_POST['item_date']) ? $_POST['item_date'] : null;

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

    if (empty($title) || empty($category) || empty($status)) {
        $error = "Please fill in all required fields.";
    }

    if (empty($error)) {
        if ($image_stream !== null) {
            // New photo uploaded: replace the old one
            $stmt = $pdo->prepare("UPDATE items SET title=?, description=?, category=?, status=?, location=?, item_date=?, image_data=?, image_mime=? WHERE id=? AND user_id=?");
            $stmt->bindValue(1, $title);
            $stmt->bindValue(2, $description);
            $stmt->bindValue(3, $category);
            $stmt->bindValue(4, $status);
            $stmt->bindValue(5, $location);
            $stmt->bindValue(6, $item_date);
            $stmt->bindValue(7, $image_stream, PDO::PARAM_LOB);
            $stmt->bindValue(8, $image_mime);
            $stmt->bindValue(9, $id, PDO::PARAM_INT);
            $stmt->bindValue(10, $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // No new photo: leave image_data and image_mime untouched
            $stmt = $pdo->prepare("UPDATE items SET title=?, description=?, category=?, status=?, location=?, item_date=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $description, $category, $status, $location, $item_date, $id, $_SESSION['user_id']]);
        }

        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Item - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Lost & Found</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="edit_item.php?id=<?= $item['id'] ?>" enctype="multipart/form-data">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>

            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($item['description']) ?></textarea>

            <label>Category</label>
            <select name="category" required>
                <?php foreach (['Electronics','Documents','Bags','Clothing','Accessories','Other'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= $item['category'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>

            <label>Status</label>
            <select name="status" required>
                <option value="lost" <?= $item['status'] == 'lost' ? 'selected' : '' ?>>Lost</option>
                <option value="found" <?= $item['status'] == 'found' ? 'selected' : '' ?>>Found</option>
                <option value="claimed" <?= $item['status'] == 'claimed' ? 'selected' : '' ?>>Claimed</option>
            </select>

            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($item['location']) ?>">

            <label>Date</label>
            <input type="date" name="item_date" value="<?= $item['item_date'] ?>">

            <label>Current Photo</label>
            <?php if ($item['has_image']): ?>
                <img src="image.php?id=<?= (int)$item['id'] ?>" style="width:100px; display:block; margin-bottom:10px;">
            <?php else: ?>
                <p>No photo yet.</p>
            <?php endif; ?>

            <label>Replace Photo (optional)</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Update Item</button>
        </form>
    </div>
    <script src="app.js"></script>
</body>
</html>