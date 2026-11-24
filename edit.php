<?php
require_once 'includes/helpers.php';
require_once 'config/db.php';

$userId = requireAuth();
$entryId = (int)($_GET['id'] ?? $_POST['entry_id'] ?? 0);

if ($entryId <= 0) {
    header('Location: diary.php');
    exit;
}

$allowedMoods = getAllowedMoods();

$stmt = $pdo->prepare('SELECT id, mood, title, content FROM entries WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$entryId, $userId]);
$entry = $stmt->fetch();

if (!$entry) {
    header('Location: diary.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_entry'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Қауіпсіздік токені жарамсыз. Бетті жаңартып қайта көріңіз.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $mood = trim($_POST['mood'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (mb_strlen($title) < 3) {
            $errors[] = 'Тақырып кемінде 3 таңбадан тұруы қажет.';
        }
        if (!array_key_exists($mood, $allowedMoods)) {
            $errors[] = 'Тізімнен дұрыс көңіл-күйді таңдаңыз.';
        }
        if (mb_strlen($content) < 5) {
            $errors[] = 'Жазба мәтіні тым қысқа (кемінде 5 таңба).';
        }

        if (empty($errors)) {
            $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
            $catStmt->execute([$mood]);
            $catRow = $catStmt->fetch();
            $categoryId = $catRow ? $catRow['id'] : null;

            $updateStmt = $pdo->prepare(
                'UPDATE entries SET category_id = ?, mood = ?, title = ?, content = ? WHERE id = ? AND user_id = ?'
            );
            $updateStmt->execute([$categoryId, $mood, $title, $content, $entryId, $userId]);

            header('Location: diary.php?updated=1');
            exit;
        }

        $entry['title'] = $title;
        $entry['mood'] = $mood;
        $entry['content'] = $content;
    }
}

$pageTitle = 'Жазбаны өңдеу — Менің Күнделігім';
$activePage = 'diary';
include 'includes/header.php';
?>

<main class="container diary-container">
    <div class="edit-card">
        <div class="diary-header">
            <h1>Жазбаны өңдеу</h1>
            <a href="diary.php" class="btn btn-outline">← Артқа қайту</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p>• <?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int)$entry['id'] ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
            <input type="hidden" name="update_entry" value="1">
            <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">

            <div class="form-group">
                <label for="editTitle">Тақырыбы</label>
                <input type="text" id="editTitle" name="title" value="<?= e($entry['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="editMood">Көңіл-күй</label>
                <select id="editMood" name="mood" required>
                    <?php foreach ($allowedMoods as $moodKey => $moodLabel): ?>
                        <option value="<?= e($moodKey) ?>" <?= $entry['mood'] === $moodKey ? 'selected' : '' ?>>
                            <?= e($moodLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="editContent">Жазба мәтіні</label>
                <textarea id="editContent" name="content" rows="7" required><?= e($entry['content']) ?></textarea>
            </div>

            <div class="hero-buttons">
                <button type="submit" class="btn btn-primary">Өзгерістерді сақтау</button>
                <a href="diary.php" class="btn btn-outline">Бас тарту</a>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
