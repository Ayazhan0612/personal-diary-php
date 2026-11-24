<?php
$pageTitle = 'Менің Күнделігім — Жеке ойлар кеңістігі';
$activePage = 'index';
include 'includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container hero-content">
            <h1>Күнделікті ойларыңыз бен маңызды сәттеріңізді сақтаңыз</h1>
            <p>Қауіпсіз, ыңғайлы және тек өзіңізге ғана қолжетімді онлайн жеке күнделік платформасы.</p>
            <div class="hero-buttons">
                <a href="auth.php?tab=register" class="btn btn-primary">Күнделік бастау</a>
                <a href="diary.php" class="btn btn-outline">Демо нұсқаны көру</a>
            </div>
        </div>
    </section>

    <section class="features container">
        <div class="feature-card">
            <span class="feature-icon">🔒</span>
            <h3>Толық құпиялылық</h3>
            <p>Әр пайдаланушының жазбалары жеке аккаунтпен және құпиясөзбен қорғалады.</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">😊</span>
            <h3>Көңіл-күй белгілері</h3>
            <p>Әр күннің эмоциясы мен санатын белгілеп, өзіңізді бақылаңыз.</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">🔍</span>
            <h3>Жылдам іздеу және сүзу</h3>
            <p>Кез келген естелікті күні, санаты немесе кілт сөздер арқылы оңай табыңыз.</p>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
