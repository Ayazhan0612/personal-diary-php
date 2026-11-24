<?php
session_start();
require_once 'config/db.php';

// Тек авторизациядан өткен пайдаланушыларға рұқсат беру
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

// Формаларды қорғау үшін CSRF-токен жасау
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = (int)$_SESSION['user_id'];
$errors = [];
$successMsg = '';

// Пайдаланушы деректерін алу
$stmt = $pdo->prepare('SELECT id, username, email, password, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Қауіпсіздік токені жарамсыз. Бетті жаңартып қайта көріңіз.';
    } else {
        $action = $_POST['action'] ?? '';

        // 1. Аты-жөнін жаңарту
        if ($action === 'update_profile') {
            $newUsername = trim($_POST['username'] ?? '');
            if (mb_strlen($newUsername) < 2) {
                $errors[] = 'Аты-жөніңіз кемінде 2 таңбадан тұруы керек.';
            } else {
                $updStmt = $pdo->prepare('UPDATE users SET username = ? WHERE id = ?');
                $updStmt->execute([$newUsername, $userId]);
                $_SESSION['username'] = $newUsername;
                $user['username'] = $newUsername;
                $successMsg = 'Профиль деректері сәтті жаңартылды!';
            }
        }

        // 2. Құпиясөзді өзгерту
        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = 'Қазіргі құпиясөз қате енгізілді.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'Жаңа құпиясөз кемінде 6 таңба болуы тиіс.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'Жаңа құпиясөздер сәйкес келмейді.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $passStmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $passStmt->execute([$hashedPassword, $userId]);
                $successMsg = 'Құпиясөз сәтті өзгертілді!';
            }
        }
    }
}

$pageTitle = 'Жеке профиль — Менің Күнделігім';
$activePage = 'profile';
include 'includes/header.php';
?>

<main class="container diary-container">
    <div class="edit-card">
        <div class="diary-header">
            <div>
                <h1>Жеке профиль</h1>
                <p class="subtitle">Тіркелген күні: <?= htmlspecialchars(date('d.m.Y', strtotime($user['created_at']))) ?></p>
            </div>
            <a href="diary.php" class="btn btn-outline">← Күнделікке</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p>• <?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($successMsg) ?></p>
            </div>
        <?php endif; ?>

        <!-- НЕГІЗГІ ДЕРЕКТЕРДІ ӨЗГЕРТУ -->
        <form action="profile.php" method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="update_profile">
            <h3 style="margin-bottom: 1rem;">Негізгі ақпарат</h3>

            <div class="form-group">
                <label for="profEmail">Электрондық пошта (өзгертілмейді)</label>
                <input type="email" id="profEmail" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="profName">Аты-жөніңіз</label>
                <input type="text" id="profName" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Аты-жөнді сақтау</button>
        </form>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin-bottom: 2rem;">

        <!-- ҚҰПИЯСӨЗДІ ӨЗГЕРТУ -->
        <form action="profile.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="change_password">
            <h3 style="margin-bottom: 1rem;">Құпиясөзді жаңарту</h3>

            <div class="form-group">
                <label for="currentPass">Қазіргі құпиясөз</label>
                <input type="password" id="currentPass" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="newPass">Жаңа құпиясөз (кемінде 6 таңба)</label>
                <input type="password" id="newPass" name="new_password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirmPass">Жаңа құпиясөзді растаңыз</label>
                <input type="password" id="confirmPass" name="confirm_password" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary">Құпиясөзді өзгерту</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
