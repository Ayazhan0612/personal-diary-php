<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * XSS шабуылдарынан қорғау үшін мәтінді қауіпсіз шығару
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Пайдаланушының жүйеге кіргенін тексеру және ID қайтару
 */
function requireAuth(): int
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: auth.php');
        exit;
    }
    return (int)$_SESSION['user_id'];
}

/**
 * Сессиялық CSRF-токен жасау немесе алу
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Жіберілген CSRF-токенді тексеру
 */
function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Қолжетімді көңіл-күй санаттарының ортақ тізімі
 */
function getAllowedMoods(): array
{
    return [
        'Қуанышты' => '😊 Қуанышты',
        'Ойлы'     => '🤔 Ойлы',
        'Тыныш'    => '😌 Тыныш',
        'Шабытты'  => '✨ Шабытты',
    ];
}
