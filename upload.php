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
$userId = $_COOKIE['userId'];

// Проверяем, что запрос пришел методом POST и все поля заполнены
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $storageType = $_POST['storageType'];
    $borderColor = $_POST['borderColor'];
    $description = $_POST['description'];

    // Проверка полей
    if (empty($storageType) || empty($_FILES['fileInput']) || empty($borderColor) || empty($description)) {
        echo 'Все поля обязательны для заполнения!';
        exit;
    }

    // Валидация описания
    if (strlen($description) > 100) {
        echo 'Описание не должно превышать 100 символов!';
        exit;
    }

    // Валидация файла (размер до 1MB, тип — JPG/PNG)
    $file = $_FILES['fileInput'];
    $fileSize = $file['size'];
    $fileType = mime_content_type($file['tmp_name']);

    if ($fileSize > 1048576) {
        echo 'Размер файла не должен превышать 1MB!';
        exit;
    }

    if (!in_array($fileType, ['image/jpeg', 'image/png'])) {
        echo 'Разрешены только форматы JPG и PNG!';
        exit;
    }

    // Определяем место хранения
    if ($storageType === 'filesystem') {
        // Хранение в файловой системе
        $targetDir = 'uploads/';
        $fileName = uniqid() . '-' . basename($file['name']);
        $targetFile = $targetDir . $fileName;

        // Перемещаем файл в папку uploads/
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // Сохраняем путь к изображению для базы данных
        } else {
            echo 'Ошибка при загрузке файла!';
            exit;
        }
    } elseif ($storageType === 'imgur') {
        // Загрузка на Imgur через API
        $clientId = '2b283aa908c3ee0'; // Замените на свой Client ID
        $imageData = base64_encode(file_get_contents($file['tmp_name']));

        // Настройка запроса к Imgur API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $clientId));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $imageData));

        $response = curl_exec($ch);
        curl_close($ch);

        $imgurResponse = json_decode($response, true);
        if ($imgurResponse['success']) {
            $imagePath = $imgurResponse['data']['link']; // Сохраняем ссылку на изображение
        } else {
            echo 'Ошибка при загрузке на Imgur!';
            exit;
        }
    }

    // Сохраняем данные в базу данных, включая дату загрузки
    $stmt = $pdo->prepare("INSERT INTO images (user_id, image_path, border_color, description, upload_date) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$userId, $imagePath, $borderColor, $description])) {
        echo 'Изображение успешно загружено!';
    } else {
        echo 'Ошибка при сохранении в базе данных!';
    }

} else {
    echo 'Неверный запрос!';
}
?>
