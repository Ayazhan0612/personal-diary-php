<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'Менің Күнделігім';
$activePage = $activePage ?? '';
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
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Мәзірді ашу">☰</button>
            <nav class="nav-links" id="navLinks">
                <a href="index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Басты бет</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="diary.php" class="<?= $activePage === 'diary' ? 'active' : '' ?>">Күнделік</a>
                    <a href="profile.php" class="user-greeting <?= $activePage === 'profile' ? 'active' : '' ?>">
                        👤 <?= htmlspecialchars($_SESSION['username'] ?? 'Профиль') ?>
                    </a>
                    <a href="logout.php" class="btn-nav btn-logout">Шығу</a>
                <?php else: ?>
                    <a href="auth.php" class="btn-nav <?= $activePage === 'auth' ? 'active' : '' ?>">Кіру / Тіркелу</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
