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

$allowedFonts = [
    "'Inter', sans-serif"            => 'Inter (Заманауи)',
    "'Montserrat', sans-serif"       => 'Montserrat (Геометриялық)',
    "'Nunito', sans-serif"           => 'Nunito (Жұмсақ)',
    "'Rubik', sans-serif"            => 'Rubik (Дөңгелек)',
    "'Comfortaa', cursive"           => 'Comfortaa (Ерекше)',
    "'Lora', serif"                  => 'Lora (Классикалық)',
    "'Playfair Display', serif"      => 'Playfair Display (Эстетикалық)',
    "'Merriweather', serif"          => 'Merriweather (Кітаби)',
    "'JetBrains Mono', monospace"    => 'JetBrains Mono (Код стилі)',
    "'Caveat', cursive"              => 'Caveat (Қолжазба)'
];

$stmt = $pdo->prepare('SELECT id, mood, title, content, font_family, image_path FROM entries WHERE id = ? AND user_id = ? LIMIT 1');
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
        $fontFamily = trim($_POST['font_family'] ?? "'Inter', sans-serif");
        $content = trim($_POST['content'] ?? '');
        $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
        $imagePath = $entry['image_path'];

        if (mb_strlen($title) < 3) {
            $errors[] = 'Тақырып кемінде 3 таңбадан тұруы қажет.';
        }
        if (!array_key_exists($mood, $allowedMoods)) {
            $errors[] = 'Тізімнен дұрыс көңіл-күйді таңдаңыз.';
        }
        if (!array_key_exists($fontFamily, $allowedFonts)) {
            $fontFamily = "'Inter', sans-serif";
        }
        if (mb_strlen($content) < 5) {
            $errors[] = 'Жазба мәтіні тым қысқа (кемінде 5 таңба).';
        }

        // Ескі суретті өшіру таңдалса
        if ($removeImage && !empty($imagePath)) {
            if (file_exists(__DIR__ . '/' . $imagePath)) {
                unlink(__DIR__ . '/' . $imagePath);
            }
            $imagePath = null;
        }

        // Жаңа сурет жүктелсе
        if (isset($_FILES['entry_image']) && $_FILES['entry_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['entry_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['entry_image']['tmp_name'];
                $fileSize = $_FILES['entry_image']['size'];
                $fileInfo = getimagesize($fileTmp);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

                if ($fileInfo === false || !in_array($fileInfo['mime'], $allowedTypes, true)) {
                    $errors[] = 'Тек JPG, PNG, WEBP немесе GIF суреттерін жүктеуге болады.';
                } elseif ($fileSize > 5 * 1024 * 1024) {
                    $errors[] = 'Сурет көлемі 5 МБ-тан аспауы керек.';
                } else {
                    $uploadDir = __DIR__ . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $ext = pathinfo($_FILES['entry_image']['name'], PATHINFO_EXTENSION);
                    $newFileName = 'img_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                    if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                        // Ескі сурет болса, серверден өшіреміз
                        if (!empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath)) {
                            unlink(__DIR__ . '/' . $imagePath);
                        }
                        $imagePath = 'uploads/' . $newFileName;
                    } else {
                        $errors[] = 'Суретті серверге сақтау кезінде қате кетті.';
                    }
                }
            } else {
                $errors[] = 'Суретті жүктеу сәтсіз аяқталды.';
            }
        }

        if (empty($errors)) {
            $catStmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
            $catStmt->execute([$mood]);
            $catRow = $catStmt->fetch();
            $categoryId = $catRow ? $catRow['id'] : null;

            $updateStmt = $pdo->prepare(
                'UPDATE entries SET category_id = ?, mood = ?, title = ?, content = ?, font_family = ?, image_path = ? WHERE id = ? AND user_id = ?'
            );
            $updateStmt->execute([$categoryId, $mood, $title, $content, $fontFamily, $imagePath, $entryId, $userId]);

            header('Location: diary.php?updated=1');
            exit;
        }

        $entry['title'] = $title;
        $entry['mood'] = $mood;
        $entry['font_family'] = $fontFamily;
        $entry['content'] = $content;
        $entry['image_path'] = $imagePath;
    }
}

$currentFont = !empty($entry['font_family']) ? $entry['font_family'] : "'Inter', sans-serif";
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

        <form action="edit.php?id=<?= (int)$entry['id'] ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
            <input type="hidden" name="update_entry" value="1">
            <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">

            <div class="form-group">
                <label for="editTitle">Тақырыбы</label>
                <input type="text" id="editTitle" name="title" value="<?= e($entry['title']) ?>" style="font-family: <?= e($currentFont) ?> !important;" required>
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
                <label for="editFontSelect">Жазба шрифті (Әріп стилі)</label>
                <select id="editFontSelect" name="font_family">
                    <?php foreach ($allowedFonts as $fontCss => $fontLabel): ?>
                        <option value="<?= e($fontCss) ?>" <?= $currentFont === $fontCss ? 'selected' : '' ?>>
                            <?= e($fontLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="editImage">Сурет (жаңасын таңдасаңыз, ескісі ауыстырылады)</label>
                <?php if (!empty($entry['image_path'])): ?>
                    <div style="margin-bottom: 0.8rem;">
                        <img src="<?= e($entry['image_path']) ?>" alt="Қазіргі сурет" class="entry-image" style="max-height: 180px;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.4rem; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" name="remove_image" value="1" style="width: auto;">
                            Қазіргі суретті өшіру
                        </label>
                    </div>
                <?php endif; ?>
                <input type="file" id="editImage" name="entry_image" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>

            <div class="form-group">
                <label for="editContent">Жазба мәтіні</label>
                <textarea id="editContent" name="content" rows="7" style="font-family: <?= e($currentFont) ?> !important;" required><?= e($entry['content']) ?></textarea>
            </div>

            <div class="hero-buttons">
                <button type="submit" class="btn btn-primary">Өзгерістерді сақтау</button>
                <a href="diary.php" class="btn btn-outline">Бас тарту</a>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fontSelect = document.getElementById('editFontSelect');
    const titleInput = document.getElementById('editTitle');
    const contentArea = document.getElementById('editContent');

    if (fontSelect && titleInput && contentArea) {
        fontSelect.addEventListener('change', (e) => {
            const chosenFont = e.target.value;
            titleInput.style.setProperty('font-family', chosenFont, 'important');
            contentArea.style.setProperty('font-family', chosenFont, 'important');
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
