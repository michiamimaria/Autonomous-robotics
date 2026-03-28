<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/db.php';

$flashOk = $_SESSION['flash_ok'] ?? null;
$flashErr = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

$recent = [];
$dbError = false;
try {
    $recent = fetch_recent_commands(12);
} catch (Throwable) {
    $dbError = true;
}

$destinations = [
    'A' => ['label' => 'Room A', 'hint' => 'North wing', 'icon' => 'A'],
    'B' => ['label' => 'Room B', 'hint' => 'Library side', 'icon' => 'B'],
    'C' => ['label' => 'Room C', 'hint' => 'Cafeteria', 'icon' => 'C'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart Campus Delivery Robot — dispatch and track deliveries">
    <title>Campus Delivery — Control</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="bg-grid" aria-hidden="true"></div>
    <header class="top">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true"></span>
            <div>
                <h1 class="brand-title">Campus Delivery</h1>
                <p class="brand-sub">Autonomous robot control</p>
            </div>
        </div>
        <div class="status-pill" title="System status">
            <span class="dot dot--live"></span>
            Online
        </div>
    </header>

    <main class="main">
        <?php if ($flashOk !== null): ?>
            <div class="toast toast--ok" role="status"><?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($flashErr !== null): ?>
            <div class="toast toast--err" role="alert"><?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel panel--hero">
            <h2 class="section-title">Send the robot</h2>
            <p class="section-lead">Pick a drop-off point. The command is logged for your campus demo.</p>

            <form class="dispatch" method="post" action="send.php" id="dispatch-form">
                <fieldset class="dest-grid">
                    <legend class="sr-only">Destination</legend>
                    <?php foreach ($destinations as $code => $d): ?>
                        <label class="dest-card">
                            <input type="radio" name="location" value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" required>
                            <span class="dest-card__inner">
                                <span class="dest-card__badge"><?= htmlspecialchars($d['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="dest-card__label"><?= htmlspecialchars($d['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="dest-card__hint"><?= htmlspecialchars($d['hint'], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <button type="submit" class="btn btn--primary" id="dispatch-btn">
                    <span class="btn__text">Dispatch robot</span>
                    <span class="btn__spinner" aria-hidden="true"></span>
                </button>
            </form>
        </section>

        <section class="panel">
            <div class="section-head">
                <h2 class="section-title">Recent commands</h2>
                <?php if (!$dbError && $recent !== []): ?>
                    <span class="badge"><?= count($recent) ?> latest</span>
                <?php endif; ?>
            </div>

            <?php if ($dbError): ?>
                <p class="empty">Database unavailable. Start MySQL and import <code>database/schema.sql</code>.</p>
            <?php elseif ($recent === []): ?>
                <p class="empty">No deliveries yet. Dispatch the robot to see history here.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Destination</th>
                                <th scope="col">Queued</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $row): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td>
                                        <span class="pill"><?= htmlspecialchars($destinations[$row['location']]['label'] ?? ('Room ' . $row['location']), ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td><time datetime="<?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></time></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="foot">
        <p>Smart Campus Delivery Robot · PHP + MySQL</p>
    </footer>

    <script src="assets/app.js" defer></script>
</body>
</html>
