<?php
session_start();
require 'config/db.php';

// Handle search/filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$query = "SELECT items.*, users.name AS poster_name, users.email AS poster_email FROM items JOIN users ON items.user_id = users.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (items.title ILIKE ? OR items.description ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $query .= " AND items.category = ?";
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    $query .= " AND items.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY items.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Items - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Lost & Found</h2>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="add_item.php">+ Post Item</a>
            <a href="contact.php">Contact</a>
            <a href="logout.php">Logout</a>
            <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="contact.php">Contact</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero">
        <h1>Lost & Found Portal</h1>
        <p>Report lost items or help reunite someone with theirs.</p>
    </div>

    <div class="container">
        <h3>All Reported Items</h3>

        <form method="GET" action="index.php" class="filter-bar">
            <input type="text" name="search" placeholder="Search by title/description" value="<?= htmlspecialchars($search) ?>">

            <select name="category">
                <option value="">All Categories</option>
                <?php foreach (['Electronics','Documents','Bags','Clothing','Accessories','Other'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= $category_filter == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status">
                <option value="">All Statuses</option>
                <option value="lost" <?= $status_filter == 'lost' ? 'selected' : '' ?>>Lost</option>
                <option value="found" <?= $status_filter == 'found' ? 'selected' : '' ?>>Found</option>
                <option value="claimed" <?= $status_filter == 'claimed' ? 'selected' : '' ?>>Claimed</option>
            </select>

            <button type="submit" style="padding:8px 16px;">Filter</button>
        </form>

        <?php if (empty($items)): ?>
            <p>No items found.</p>
        <?php else: ?>
            <div class="items-grid">
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <?php if ($item['image_path']): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item image">
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($item['title']) ?></h4>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-<?= $item['status'] ?>"><?= htmlspecialchars($item['status']) ?></span></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                        <p><strong>Posted by:</strong> <?= htmlspecialchars($item['poster_name']) ?></p>
                        <p class="contact-info">
                            <strong>Contact:</strong>
                            <a href="mailto:<?= htmlspecialchars($item['poster_email']) ?>?subject=Regarding your <?= urlencode($item['status']) ?> item: <?= urlencode($item['title']) ?>">
                                <?= htmlspecialchars($item['poster_email']) ?>
                            </a>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="app.js"></script>
</body>
</html>