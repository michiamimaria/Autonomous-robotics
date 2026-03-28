<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Local SQLite when MySQL is unavailable.
 */
function pdo_sqlite(array $opts): PDO
{
    $root = dirname(__DIR__);
    $dir = $root . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create data directory: ' . $dir);
    }
    $path = $dir . DIRECTORY_SEPARATOR . 'robot.sqlite';
    $pdo = new PDO('sqlite:' . $path, null, null, $opts);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS commands (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            location VARCHAR(10) NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )'
    );

    return $pdo;
}

function data_dir(): string
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create data directory: ' . $dir);
    }

    return $dir;
}

function file_commands_path(): string
{
    return data_dir() . DIRECTORY_SEPARATOR . 'commands.json';
}

/**
 * @return PDO|null Null when no PDO drivers — use JSON file storage instead.
 */
function pdo_instance(): ?PDO
{
    global $dbHost, $dbUser, $dbPass, $dbName;

    static $pdo = null;
    static $resolved = false;

    if ($resolved) {
        return $pdo;
    }
    $resolved = true;

    if (!extension_loaded('pdo')) {
        return null;
    }

    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if (extension_loaded('pdo_mysql')) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, $opts);
            return $pdo;
        } catch (PDOException $e) {
            if (!extension_loaded('pdo_sqlite')) {
                return null;
            }
            try {
                $pdo = pdo_sqlite($opts);
                return $pdo;
            } catch (Throwable) {
                return null;
            }
        }
    }

    if (extension_loaded('pdo_sqlite')) {
        try {
            $pdo = pdo_sqlite($opts);
            return $pdo;
        } catch (Throwable) {
            return null;
        }
    }

    return null;
}

function file_insert_command(string $location): void
{
    $path = file_commands_path();
    $data = [];
    if (is_file($path)) {
        $raw = file_get_contents($path);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }
    }
    $maxId = 0;
    foreach ($data as $row) {
        if (isset($row['id'])) {
            $maxId = max($maxId, (int) $row['id']);
        }
    }
    $data[] = [
        'id' => $maxId + 1,
        'location' => $location,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    if (file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) {
        throw new RuntimeException('Could not write ' . $path);
    }
}

/**
 * @return list<array{id:int, location:string, created_at:string}>
 */
function file_fetch_recent_commands(int $limit): array
{
    $path = file_commands_path();
    if (!is_file($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return [];
    }
    usort($data, static function ($a, $b): int {
        return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
    });
    $data = array_slice($data, 0, $limit);
    $rows = [];
    foreach ($data as $row) {
        $rows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'location' => (string) ($row['location'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    return $rows;
}

/**
 * Save one delivery command (MySQL, SQLite, or JSON file).
 */
function insert_command(string $location): void
{
    $pdo = pdo_instance();
    if ($pdo instanceof PDO) {
        $stmt = $pdo->prepare('INSERT INTO commands (location) VALUES (?)');
        $stmt->execute([$location]);
        return;
    }
    file_insert_command($location);
}

/**
 * @return list<array{id:int, location:string, created_at:string}>
 */
function fetch_recent_commands(int $limit = 12): array
{
    $limit = max(1, min(50, $limit));
    $pdo = pdo_instance();
    if ($pdo instanceof PDO) {
        $sql = 'SELECT id, location, created_at FROM commands ORDER BY id DESC LIMIT ' . (int) $limit;
        $stmt = $pdo->query($sql);
        if ($stmt === false) {
            return [];
        }
        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                'id' => (int) $row['id'],
                'location' => (string) $row['location'],
                'created_at' => (string) $row['created_at'],
            ];
        }

        return $rows;
    }

    return file_fetch_recent_commands($limit);
}
