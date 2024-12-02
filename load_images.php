<?php
include 'config.php';
require_once 'Database.php';

// Создаем объект класса Database
$db = new Database($dbConfig['host'], $dbConfig['db'], $dbConfig['user'], $dbConfig['password']);

// Получаем PDO объект
$pdo = $db->getPDO();

// Проверяем наличие уникального идентификатора пользователя
if (!isset($_COOKIE['userId'])) {
    die("Ошибка: не удалось получить идентификатор пользователя.");
}

// Получаем уникальный идентификатор пользователя из cookies
$userId = $_COOKIE['userId'];

// Разрешенные параметры сортировки
$allowedSorts = ['upload_date DESC', 'upload_date ASC', 'description ASC', 'description DESC'];
$orderBy = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'upload_date DESC';

// Запрашиваем изображения пользователя из базы данных
$stmt = $pdo->prepare("SELECT * FROM images WHERE user_id = ? ORDER BY $orderBy");
$stmt->execute([$userId]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отображаем изображения
foreach ($images as $image) {
    echo '<div class="card" style="width: 300px; display: inline-block; margin: 10px; border: 2px solid ' . htmlspecialchars($image['border_color']) . ';">';
    echo '<img src="' . htmlspecialchars($image['image_path']) . '" class="card-img-top" style="width: 100%; height: 300px; object-fit: contain;" alt="Image">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">' . htmlspecialchars($image['description']) . '</h5>';
    echo '</div>';
    echo '</div>';
}
?>
