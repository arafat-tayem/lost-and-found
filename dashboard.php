<?php
session_start();
require 'config/db.php';

// Protect this page - redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch this user's items
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Lost & Found</h2>
        <div>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="index.php">Browse All Items</a>
            <a href="add_item.php">+ Post Item</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h3>Your Posted Items</h3>

        <?php if (empty($items)): ?>
            <p>You haven't posted any items yet.</p>
        <?php else: ?>
            <div class="items-grid">
                <?php foreach ($items as $index => $item): ?>
                    <div class="item-card" style="animation-delay: <?= $index * 0.1 ?>s">
                        <?php if ($item['image_path']): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item image">
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($item['title']) ?></h4>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-<?= $item['status'] ?>"><?= htmlspecialchars($item['status']) ?></span></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                        <a href="edit_item.php?id=<?= $item['id'] ?>">Edit</a>
                        <a href="delete_item.php?id=<?= $item['id'] ?>" onclick="confirmDelete(event)">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>