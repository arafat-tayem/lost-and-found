<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$error = "";

// Fetch the item, but only if it belongs to this user
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
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
    $item_date = $_POST['item_date'];

    $image_path = $item['image_path']; // keep old image by default

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $destination = 'uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_path = $destination;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE items SET title=?, description=?, category=?, status=?, location=?, item_date=?, image_path=? WHERE id=? AND user_id=?");
    $stmt->execute([$title, $description, $category, $status, $location, $item_date, $image_path, $id, $_SESSION['user_id']]);

    header("Location: dashboard.php");
    exit();
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
        <h2>Edit Item</h2>
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
            <?php if ($item['image_path']): ?>
                <img src="<?= htmlspecialchars($item['image_path']) ?>" style="width:100px; display:block; margin-bottom:10px;">
            <?php endif; ?>

            <label>Replace Photo (optional)</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Update Item</button>
        </form>
    </div>
    <script src="app.js"></script>
</body>
</html>