<?php
require_once 'vendor/autoload.php'; 
include 'config.php';
require_once 'Database.php';
require_once 'ImageUploader.php'; // Подключаем классы для загрузки изображений
require_once 'ImageUploaderFactory.php'; // Подключаем фабрику для выбора способа загрузки

// Инициализируем Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

$db = new Database($dbConfig['host'], $dbConfig['db'], $dbConfig['user'], $dbConfig['password']);
$pdo = $db->getPDO();

// Проверяем тип запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'upload') {
        // Проверяем наличие уникального идентификатора пользователя
        if (!isset($_COOKIE['userId'])) {
            echo json_encode(['success' => false, 'message' => 'Ошибка: не удалось получить идентификатор пользователя.']);
            exit;
        }

        $userId = $_COOKIE['userId'];
        $storageType = $_POST['storageType'];
        $borderColor = $_POST['borderColor'];
        $description = $_POST['description'];

        // Проверяем обязательные поля
        if (empty($storageType) || empty($_FILES['fileInput']) || empty($borderColor) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения!']);
            exit;
        }

        // Проверяем длину описания
        if (strlen($description) > 100) {
            echo json_encode(['success' => false, 'message' => 'Описание не должно превышать 100 символов!']);
            exit;
        }

        // Проверяем файл
        $file = $_FILES['fileInput'];
        $fileSize = $file['size'];
        $fileType = mime_content_type($file['tmp_name']);

        if ($fileSize > 1048576) {
            echo json_encode(['success' => false, 'message' => 'Размер файла не должен превышать 1MB!']);
            exit;
        }

        if (!in_array($fileType, ['image/jpeg', 'image/png'])) {
            echo json_encode(['success' => false, 'message' => 'Разрешены только форматы JPG и PNG!']);
            exit;
        }

        // Используем фабрику для создания соответствующего загрузчика
        try {
            $uploader = ImageUploaderFactory::createUploader($pdo, $userId, $description, $borderColor, $storageType);
            if ($uploader->upload($file)) {
                echo json_encode(['success' => true, 'message' => 'Изображение успешно загружено!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при загрузке изображения!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'load_images') {
        $sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'upload_date DESC';

        $stmt = $pdo->prepare("SELECT * FROM images ORDER BY $sortOrder");
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo $twig->render('gallery.twig', ['images' => $images]);
        exit;
    }
}

echo json_encode(['error' => 'Неподдерживаемый тип запроса.']);
exit;
