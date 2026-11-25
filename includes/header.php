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
    <!-- 10 кириллицалық шрифт (Google Fonts) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;700&family=Comfortaa:wght@400;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Merriweather:wght@400;700&family=Montserrat:wght@400;600;700&family=Nunito:wght@400;600;700&family=Playfair+Display:wght@500;700&family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
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
                    <a href="profile.php" class="<?= $activePage === 'profile' ? 'active' : '' ?>">👤 <?= htmlspecialchars($_SESSION['username'] ?? '') ?></a>
                    <a href="logout.php" class="btn-nav btn-logout">Шығу</a>
                <?php else: ?>
                    <a href="diary.php" class="<?= $activePage === 'diary' ? 'active' : '' ?>">Күнделік</a>
                    <a href="auth.php" class="btn-nav <?= $activePage === 'auth' ? 'active' : '' ?>">Кіру / Тіркелу</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
