<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pageTitle)) {
    $pageTitle = 'Менің Күнделігім';
}
if (!isset($activePage)) {
    $activePage = '';
}
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">📖 Менің Күнделігім</a>
            <button class="menu-toggle" id="menuToggle">☰</button>
            <nav class="nav-links" id="navLinks">
                <a href="index.php" class="<?= $activePage === 'index' ? 'active' : '' ?>">Басты бет</a>
                <?php if ($isLoggedIn): ?>
                    <a href="diary.php" class="<?= $activePage === 'diary' ? 'active' : '' ?>">Күнделік</a>
                    <span class="user-greeting">👤 <?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                    <a href="logout.php" class="btn-nav btn-logout">Шығу</a>
                <?php else: ?>
                    <a href="diary.php" class="<?= $activePage === 'diary' ? 'active' : '' ?>">Күнделік</a>
                    <a href="auth.php" class="btn-nav <?= $activePage === 'auth' ? 'active' : '' ?>">Кіру / Тіркелу</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
