<?php
$host = 'localhost';
$dbname = 'personal_diary';
$username = 'root';
$password = ''; // XAMPP-та әдепкі бойынша бос, MAMP-та 'root'

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('Дерекқорға қосылу қатесі: ' . htmlspecialchars($e->getMessage()));
}
