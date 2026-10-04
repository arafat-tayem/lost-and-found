<?php
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    exit;
}

$stmt = $pdo->prepare('SELECT image_data, image_mime FROM items WHERE id = ?');
$stmt->execute([$id]);
$stmt->bindColumn(1, $data, PDO::PARAM_LOB);
$stmt->bindColumn(2, $mime);

if (!$stmt->fetch(PDO::FETCH_BOUND) || $data === null) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');

if (is_resource($data)) {
    fpassthru($data);
} else {
    echo $data;
}