<?php
$pageTitle = 'Кіру және Тіркелу — Менің Күнделігім';
$activePage = 'auth';

$errors = [];
$successMsg = '';
// $_GET арқылы қай қойынды (Кіру немесе Тіркелу) ашық тұратынын анықтау
$activeTab = (isset($_GET['tab']) && $_GET['tab'] === 'register') ? 'register' : 'login';

// $_POST сұранысын өңдеу және серверлік валидация
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $activeTab = 'login';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Дұрыс электрондық пошта мекенжайын енгізіңіз.';
        }
        if (empty($password)) {
            $errors[] = 'Құпиясөзді енгізіңіз.';
        }

        if (empty($errors)) {
            $successMsg = 'Деректер серверде сәтті тексерілді! (11-аптада MySQL арқылы кіру қосылады).';
        }
    } elseif ($action === 'register') {
        $activeTab = 'register';
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (mb_strlen($username) < 2) {
            $errors[] = 'Аты-жөніңіз кемінде 2 таңбадан тұруы керек.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Электрондық пошта форматы қате.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Құпиясөз кемінде 6 таңба болуы тиіс.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Енгізізілген құпиясөздер сәйкес келмейді.';
        }

        if (empty($errors)) {
            $successMsg = 'Сәтті тіркелдіңіз, ' . htmlspecialchars($username) . '! Енді жүйеге кіре аласыз.';
            $activeTab = 'login';
        }
    }
}

include 'includes/header.php';
?>

<main class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-tabs">
            <button type="button" class="tab-btn <?= $activeTab === 'login' ? 'active' : '' ?>" id="loginTab">Кіру</button>
            <button type="button" class="tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>" id="registerTab">Тіркелу</button>
        </div>

        <!-- СЕРВЕРЛІК ТЕКСЕРУ ХАБАРЛАМАЛАРЫ -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p>• <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($successMsg) ?></p>
            </div>
        <?php endif; ?>

        <!-- КІРУ ФОРМАСЫ -->
        <form id="loginForm" class="auth-form <?= $activeTab === 'login' ? '' : 'hidden' ?>" action="auth.php" method="POST">
            <input type="hidden" name="action" value="login">
            <h2>Аккаунтқа кіру</h2>
            <div class="form-group">
                <label for="loginEmail">Электрондық пошта</label>
                <input type="email" id="loginEmail" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="example@mail.kz" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Құпиясөз</label>
                <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Кіру</button>
        </form>

        <!-- ТІРКЕЛУ ФОРМАСЫ -->
        <form id="registerForm" class="auth-form <?= $activeTab === 'register' ? '' : 'hidden' ?>" action="auth.php?tab=register" method="POST">
            <input type="hidden" name="action" value="register">
            <h2>Жаңа аккаунт ашу</h2>
            <div class="form-group">
                <label for="regName">Аты-жөніңіз</label>
                <input type="text" id="regName" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Айдана Серікқызы" required>
            </div>
            <div class="form-group">
                <label for="regEmail">Электрондық пошта</label>
                <input type="email" id="regEmail" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="example@mail.kz" required>
            </div>
            <div class="form-group">
                <label for="regPassword">Құпиясөз (кемінде 6 таңба)</label>
                <input type="password" id="regPassword" name="password" minlength="6" required>
            </div>
            <div class="form-group">
                <label for="regConfirm">Құпиясөзді растаңыз</label>
                <input type="password" id="regConfirm" name="confirm_password" minlength="6" required>
            </div>
            <p id="authError" class="error-msg"></p>
            <button type="submit" class="btn btn-primary btn-full">Тіркелу</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
