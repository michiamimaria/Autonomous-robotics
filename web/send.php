<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/db.php';

$allowed = ['A', 'B', 'C'];
$location = isset($_POST['location']) ? strtoupper(trim((string) $_POST['location'])) : '';

if (!in_array($location, $allowed, true)) {
    $_SESSION['flash_error'] = 'Please choose a valid destination.';
    header('Location: index.php', true, 303);
    exit;
}

try {
    insert_command($location);
} catch (RuntimeException | PDOException $e) {
    $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
    header('Location: index.php', true, 303);
    exit;
}

$labels = ['A' => 'Room A', 'B' => 'Room B', 'C' => 'Room C'];
$_SESSION['flash_ok'] = 'Robot dispatched to ' . ($labels[$location] ?? $location) . '.';

header('Location: index.php', true, 303);
exit;
