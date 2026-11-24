<?php
require_once 'includes/helpers.php';
require_once 'config/db.php';

$userId = requireAuth();
$username = $_SESSION['username'] ?? 'Пайдаланушы';

$stmt = $pdo->prepare('SELECT title, mood, content, created_at FROM entries WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$entries = $stmt->fetchAll();

$filename = 'mening-kundeligim-' . date('Y-m-d') . '.txt';
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "========================================\n";
echo "ЖЕКЕ КҮНДЕЛІК ЖАЗБАЛАРЫ\n";
echo "Пайдаланушы: " . $username . "\n";
echo "Экспортталған күні: " . date('d.m.Y H:i') . "\n";
echo "Барлық жазбалар саны: " . count($entries) . "\n";
echo "========================================\n\n";

if (empty($entries)) {
    echo "Әзірге сақталған жазбалар жоқ.\n";
} else {
    foreach ($entries as $index => $entry) {
        $num = $index + 1;
        $date = date('d.m.Y H:i', strtotime($entry['created_at']));
        echo "#{$num}. {$entry['title']}\n";
        echo "Күні: {$date} | Көңіл-күй: {$entry['mood']}\n";
        echo "----------------------------------------\n";
        echo $entry['content'] . "\n\n\n";
    }
}
exit;
