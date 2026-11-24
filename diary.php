<?php
session_start();
require_once 'config/db.php';

// Тек жүйеге кірген пайдаланушыларға ғана рұқсат беру
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$pageTitle = 'Менің жазбаларым — Менің Күнделігім';
$activePage = 'diary';

$allowedMoods = [
    'Қуанышты' => '😊 Қуанышты',
    'Ойлы'     => '🤔 Ойлы',
    'Тыныш'    => '😌 Тыныш',
    'Шабытты'  => '✨ Шабытты'
];

$formErrors = [];
$formSuccess = '';

if (isset($_GET['added'])) {
    $formSuccess = 'Жаңа жазба дерекқорға сәтті сақталды!';
} elseif (isset($_GET['updated'])) {
    $formSuccess = 'Жазба сәтті жаңартылды!';
} elseif (isset($_GET['deleted'])) {
    $formSuccess = 'Жазба сәтті жойылды.';
}

// 1. Жаңа жазбаны MySQL-ге қосу (CREATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $title = trim($_POST['title'] ?? '');
    $mood = trim($_POST['mood'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (mb_strlen($title) < 3) {
        $formErrors[] = 'Тақырып кемінде 3 таңбадан тұруы қажет.';
    }
    if (!array_key_exists($mood, $allowedMoods)) {
        $formErrors[] = 'Тізімнен дұрыс көңіл-күйді таңдаңыз.';
    }
    if (mb_strlen($content) < 5) {
        $formErrors[] = 'Жазба мәтіні тым қысқа (кемінде 5 таңба).';
    }

    if (empty($formErrors)) {
        $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
        $catStmt->execute([$mood]);
        $catRow = $catStmt->fetch();
        $categoryId = $catRow ? $catRow['id'] : null;

        $insertStmt = $pdo->prepare(
            'INSERT INTO entries (user_id, category_id, mood, title, content) VALUES (?, ?, ?, ?, ?)'
        );
        $insertStmt->execute([$userId, $categoryId, $mood, $title, $content]);

        header('Location: diary.php?added=1');
        exit;
    }
}

// 2. Жазбаны өшіру (DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_entry'])) {
    $entryId = (int)($_POST['entry_id'] ?? 0);
    if ($entryId > 0) {
        $delStmt = $pdo->prepare('DELETE FROM entries WHERE id = ? AND user_id = ?');
        $delStmt->execute([$entryId, $userId]);
    }
    header('Location: diary.php?deleted=1');
    exit;
}

// 3. Пайдаланушы статистикасын есептеу
$statsStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM entries WHERE user_id = ?');
$statsStmt->execute([$userId]);
$totalEntries = (int)$statsStmt->fetchColumn();

$topMoodStmt = $pdo->prepare(
    'SELECT mood, COUNT(*) AS cnt FROM entries WHERE user_id = ? GROUP BY mood ORDER BY cnt DESC LIMIT 1'
);
$topMoodStmt->execute([$userId]);
$topMoodRow = $topMoodStmt->fetch();
$topMoodLabel = $topMoodRow ? ($allowedMoods[$topMoodRow['mood']] ?? $topMoodRow['mood']) : 'Әзірге жоқ';

// 4. Пайдаланушының жазбаларын categories кестесімен біріктіріп оқу (READ + JOIN)
$searchQuery = trim($_GET['search'] ?? '');
$selectedMood = $_GET['mood'] ?? 'all';

$sql = 'SELECT e.id, e.mood, e.title, e.content, e.created_at, c.name AS category_name
        FROM entries e
        LEFT JOIN categories c ON e.category_id = c.id
        WHERE e.user_id = :user_id';
$params = ['user_id' => $userId];

if ($selectedMood !== 'all' && array_key_exists($selectedMood, $allowedMoods)) {
    $sql .= ' AND e.mood = :mood';
    $params['mood'] = $selectedMood;
}

if ($searchQuery !== '') {
    $sql .= ' AND (e.title LIKE :search_title OR e.content LIKE :search_content)';
    $params['search_title'] = '%' . $searchQuery . '%';
    $params['search_content'] = '%' . $searchQuery . '%';
}

$sql .= ' ORDER BY e.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filteredEntries = $stmt->fetchAll();

include 'includes/header.php';
?>

<main class="container diary-container">
    <div class="diary-header">
        <div>
            <h1>Менің күнделік жазбаларым</h1>
            <p class="subtitle">Сәлем, <?= htmlspecialchars($_SESSION['username'] ?? 'Пайдаланушы') ?>! Бүгін ойыңызда не бар?</p>
        </div>
        <button type="button" class="btn btn-primary" id="openModalBtn">+ Жаңа жазба</button>
    </div>

    <!-- СТАТИСТИКА БЛОГЫ -->
    <div class="stats-bar">
        <div class="stat-card">
            <span class="stat-label">Барлық жазбалар саны</span>
            <strong class="stat-value"><?= $totalEntries ?></strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">Жиі кездесетін көңіл-күй</span>
            <strong class="stat-value"><?= htmlspecialchars($topMoodLabel) ?></strong>
        </div>
    </div>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-error">
            <?php foreach ($formErrors as $err): ?>
                <p>• <?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($formSuccess)): ?>
        <div class="alert alert-success">
            <p><?= htmlspecialchars($formSuccess) ?></p>
        </div>
    <?php endif; ?>

    <!-- ІЗДЕУ ЖӘНЕ СҮЗУ ФОРМАСЫ -->
    <form class="filter-bar" action="diary.php" method="GET">
        <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Тақырып немесе мәтін бойынша іздеу...">
        <select name="mood" id="moodFilter">
            <option value="all" <?= $selectedMood === 'all' ? 'selected' : '' ?>>Барлық көңіл-күй</option>
            <?php foreach ($allowedMoods as $moodKey => $moodLabel): ?>
                <option value="<?= htmlspecialchars($moodKey) ?>" <?= $selectedMood === $moodKey ? 'selected' : '' ?>>
                    <?= htmlspecialchars($moodLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Іздеу</button>
        <?php if ($searchQuery !== '' || $selectedMood !== 'all'): ?>
            <a href="diary.php" class="btn btn-outline">Тазалау</a>
        <?php endif; ?>
    </form>

    <!-- ЖАЗБАЛАР ТІЗІМІ -->
    <div class="entries-grid" id="entriesGrid">
        <?php if (empty($filteredEntries)): ?>
            <p class="subtitle">Әзірге жазбалар табылмады. Алғашқы естелігіңізді қосыңыз!</p>
        <?php else: ?>
            <?php foreach ($filteredEntries as $item): ?>
                <?php
                    $formattedDate = date('d.m.Y H:i', strtotime($item['created_at']));
                    $moodKey = $item['category_name'] ?? $item['mood'];
                    $badgeText = $allowedMoods[$moodKey] ?? htmlspecialchars($moodKey);
                ?>
                <article class="entry-card" data-mood="<?= htmlspecialchars($moodKey) ?>">
                    <div>
                        <div class="entry-meta">
                            <span class="entry-date"><?= htmlspecialchars($formattedDate) ?></span>
                            <span class="mood-badge"><?= htmlspecialchars($badgeText) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($item['content'])) ?></p>
                    </div>
                    <div class="entry-actions">
                        <a href="edit.php?id=<?= (int)$item['id'] ?>" class="btn-small btn-edit">Өңдеу</a>
                        <form action="diary.php" method="POST" onsubmit="return confirm('Бұл жазбаны өшіруге сенімдісіз бе?');">
                            <input type="hidden" name="delete_entry" value="1">
                            <input type="hidden" name="entry_id" value="<?= (int)$item['id'] ?>">
                            <button type="submit" class="btn-small btn-delete">Жою</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- ЖАҢА ЖАЗБА ҚОСУ МОДАЛЬДЫ ТЕРЕЗЕСІ -->
<div class="modal <?= !empty($formErrors) ? '' : 'hidden' ?>" id="entryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Жаңа жазба қосу</h2>
            <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
        </div>
        <form id="newEntryForm" action="diary.php" method="POST">
            <input type="hidden" name="add_entry" value="1">
            <div class="form-group">
                <label for="entryTitle">Тақырыбы</label>
                <input type="text" id="entryTitle" name="title" value="<?= !empty($formErrors) ? htmlspecialchars($_POST['title'] ?? '') : '' ?>" placeholder="Бүгінгі күннің атауы..." required>
            </div>
            <div class="form-group">
                <label for="entryMood">Көңіл-күй</label>
                <select id="entryMood" name="mood" required>
                    <?php foreach ($allowedMoods as $moodKey => $moodLabel): ?>
                        <option value="<?= htmlspecialchars($moodKey) ?>"><?= htmlspecialchars($moodLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="entryContent">Жазба мәтіні</label>
                <textarea id="entryContent" name="content" rows="5" placeholder="Ойларыңызды осында жазыңыз..." required><?= !empty($formErrors) ? htmlspecialchars($_POST['content'] ?? '') : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Сақтау</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
