<?php
$pageTitle = 'Менің жазбаларым — Менің Күнделігім';
$activePage = 'diary';

// Бастапқы сынақ деректері (11-аптада MySQL кестесінен алынады)
$entries = [
    [
        'date' => '18.11.2026',
        'mood' => 'Шабытты',
        'badge' => '✨ Шабытты',
        'title' => 'PHP жобасын бастау',
        'content' => 'Бүгін жеке күнделік веб-қосымшасының интерфейсін жасап шықтым. Адаптивті дизайн өте жинақы көрінеді.'
    ],
    [
        'date' => '16.11.2026',
        'mood' => 'Қуанышты',
        'badge' => '😊 Қуанышты',
        'title' => 'Демалыс күнгі серуен',
        'content' => 'Тау бөктерінде таза ауада серуендеп, жаңа аптаға күш жинап қайттым. Күзгі табиғат ерекше әдемі.'
    ],
    [
        'date' => '14.11.2026',
        'mood' => 'Ойлы',
        'badge' => '🤔 Ойлы',
        'title' => 'Оқыған кітаптан түйген ойлар',
        'content' => 'Уақытты тиімді басқару және күнделікті шағын әдеттердің үлкен нәтижеге жеткізетіні туралы жазып алдым.'
    ]
];

$formErrors = [];
$formSuccess = '';

// $_POST арқылы жаңа жазба формасын өңдеу және тексеру
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $title = trim($_POST['title'] ?? '');
    $mood = trim($_POST['mood'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $allowedMoods = [
        'Қуанышты' => '😊 Қуанышты',
        'Ойлы' => '🤔 Ойлы',
        'Тыныш' => '😌 Тыныш',
        'Шабытты' => '✨ Шабытты'
    ];

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
        array_unshift($entries, [
            'date' => date('d.m.Y'),
            'mood' => $mood,
            'badge' => $allowedMoods[$mood],
            'title' => $title,
            'content' => $content
        ]);
        $formSuccess = 'Жаңа жазба сәтті тексерілді және қосылды!';
    }
}

// $_GET арқылы серверлік іздеу және сүзу
$searchQuery = trim($_GET['search'] ?? '');
$selectedMood = $_GET['mood'] ?? 'all';

$filteredEntries = array_filter($entries, function ($entry) use ($searchQuery, $selectedMood) {
    $matchesMood = ($selectedMood === 'all' || $entry['mood'] === $selectedMood);
    $matchesSearch = ($searchQuery === '' ||
        mb_stripos($entry['title'], $searchQuery) !== false ||
        mb_stripos($entry['content'], $searchQuery) !== false);
    return $matchesMood && $matchesSearch;
});

include 'includes/header.php';
?>

<main class="container diary-container">
    <div class="diary-header">
        <div>
            <h1>Менің күнделік жазбаларым</h1>
            <p class="subtitle">Бүгін ойыңызда не бар?</p>
        </div>
        <button type="button" class="btn btn-primary" id="openModalBtn">+ Жаңа жазба</button>
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

    <!-- $_GET АРҚЫЛЫ ІЗДЕУ ЖӘНЕ СҮЗУ ФОРМАСЫ -->
    <form class="filter-bar" action="diary.php" method="GET">
        <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Тақырып немесе мәтін бойынша іздеу...">
        <select name="mood" id="moodFilter">
            <option value="all" <?= $selectedMood === 'all' ? 'selected' : '' ?>>Барлық көңіл-күй</option>
            <option value="Қуанышты" <?= $selectedMood === 'Қуанышты' ? 'selected' : '' ?>>😊 Қуанышты</option>
            <option value="Ойлы" <?= $selectedMood === 'Ойлы' ? 'selected' : '' ?>>🤔 Ойлы</option>
            <option value="Тыныш" <?= $selectedMood === 'Тыныш' ? 'selected' : '' ?>>😌 Тыныш</option>
            <option value="Шабытты" <?= $selectedMood === 'Шабытты' ? 'selected' : '' ?>>✨ Шабытты</option>
        </select>
        <button type="submit" class="btn btn-primary">Іздеу</button>
        <?php if ($searchQuery !== '' || $selectedMood !== 'all'): ?>
            <a href="diary.php" class="btn btn-outline">Тазалау</a>
        <?php endif; ?>
    </form>

    <!-- ЖАЗБАЛАР ТІЗІМІ -->
    <div class="entries-grid" id="entriesGrid">
        <?php if (empty($filteredEntries)): ?>
            <p class="subtitle">Сұраныс бойынша жазбалар табылмады.</p>
        <?php else: ?>
            <?php foreach ($filteredEntries as $item): ?>
                <article class="entry-card" data-mood="<?= htmlspecialchars($item['mood']) ?>">
                    <div class="entry-meta">
                        <span class="entry-date"><?= htmlspecialchars($item['date']) ?></span>
                        <span class="mood-badge"><?= htmlspecialchars($item['badge']) ?></span>
                    </div>
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <p><?= htmlspecialchars($item['content']) ?></p>
                    <div class="entry-actions">
                        <button type="button" class="btn-small btn-edit">Өңдеу</button>
                        <button type="button" class="btn-small btn-delete">Жою</button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- ЖАҢА ЖАЗБА ҚОСУ МОДАЛЬДЫ ТЕРЕЗЕСІ ($_POST) -->
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
                    <option value="Қуанышты">😊 Қуанышты</option>
                    <option value="Ойлы">🤔 Ойлы</option>
                    <option value="Тыныш">😌 Тыныш</option>
                    <option value="Шабытты">✨ Шабытты</option>
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
