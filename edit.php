<?php
session_start();
require_once 'config/db.php';

// Тек авторизациядан өткен пайдаланушыларға рұқсат беру
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$entryId = (int)($_GET['id'] ?? $_POST['entry_id'] ?? 0);

if ($entryId <= 0) {
    header('Location: diary.php');
    exit;
}

$allowedMoods = [
    'Қуанышты' => '😊 Қуанышты',
    'Ойлы'     => '🤔 Ойлы',
    'Тыныш'    => '😌 Тыныш',
    'Шабытты'  => '✨ Шабытты'
];

// Жазбаның осы пайдаланушыға тиесілі екенін тексеру
$stmt = $pdo->prepare('SELECT id, mood, title, content FROM entries WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$entryId, $userId]);
$entry = $stmt->fetch();

if (!$entry) {
    header('Location: diary.php');
    exit;
}

$errors = [];

// Өзгерістерді сақтау (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_entry'])) {
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
                    <p>• <?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int)$entry['id'] ?>" method="POST">
            <input type="hidden" name="update_entry" value="1">
            <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">

            <div class="form-group">
                <label for="editTitle">Тақырыбы</label>
                <input type="text" id="editTitle" name="title" value="<?= htmlspecialchars($entry['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="editMood">Көңіл-күй</label>
                <select id="editMood" name="mood" required>
                    <?php foreach ($allowedMoods as $moodKey => $moodLabel): ?>
                        <option value="<?= htmlspecialchars($moodKey) ?>" <?= $entry['mood'] === $moodKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($moodLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="editContent">Жазба мәтіні</label>
                <textarea id="editContent" name="content" rows="7" required><?= htmlspecialchars($entry['content']) ?></textarea>
            </div>

            <div class="hero-buttons">
                <button type="submit" class="btn btn-primary">Өзгерістерді сақтау</button>
                <a href="diary.php" class="btn btn-outline">Бас тарту</a>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
