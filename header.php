<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Менің Күнделігім';
}
if (!isset($activePage)) {
    $activePage = '';
}
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
                <a href="diary.php" class="<?= $activePage === 'diary' ? 'active' : '' ?>">Күнделік</a>
                <a href="auth.php" class="btn-nav <?= $activePage === 'auth' ? 'active' : '' ?>">Кіру / Тіркелу</a>
            </nav>
        </div>
    </header>
