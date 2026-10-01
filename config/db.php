<?php
$url = getenv('DATABASE_URL');

if ($url) {
    // Hosted (Render): credentials come from an environment variable
    $p = parse_url($url);
    $host     = $p['host'];
    $port     = $p['port'] ?? 5432;
    $dbname   = ltrim($p['path'], '/');
    $user     = urldecode($p['user']);
    $password = urldecode($p['pass']);
    $sslmode  = 'prefer';
} else {
    // Local (XAMPP): credentials come from db.local.php (not on GitHub)
    $c = require __DIR__ . '/db.local.php';
    $host     = $c['host'];
    $port     = $c['port'];
    $dbname   = $c['dbname'];
    $user     = $c['user'];
    $password = $c['password'];
    $sslmode  = 'disable';
}

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>