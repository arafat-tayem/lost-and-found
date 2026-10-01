<?php
session_start();
require 'config/db.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if ($name && $email && $message) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Lost & Found</h2>
        <div>
            <a href="index.php">Browse All Items</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container contact-container">
        <h3>Get in Touch</h3>
        <p class="contact-subtitle">Questions, feedback, or found a bug? Send us a message.</p>

        <?php if ($success): ?>
            <div class="toast-success">✓ Message sent! We'll get back to you soon.</div>
        <?php endif; ?>

        <form method="POST" class="contact-form">
            <div class="form-group">
                <input type="text" name="name" placeholder=" " required>
                <label>Your Name</label>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder=" " required>
                <label>Your Email</label>
            </div>
            <div class="form-group">
                <textarea name="message" rows="5" placeholder=" " required></textarea>
                <label>Message</label>
            </div>
            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>