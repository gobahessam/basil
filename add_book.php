<?php
session_start();

// التحقق من تسجيل الدخول وصلاحيات المسؤول
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 1) {
    header("Location: /login.php");
    exit();
}

define("SERVERNAME", "localhost");
define("DB_LOGIN", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "library1");
define("IMG_DIR", "C:/OpenServer/domains/localhost/img/");

$connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);
if ($connect->connect_error) {
    die("Ошибка подключения: " . $connect->connect_error);
}

$error = '';
$success = '';

// جلب قائمة الناشرين
$publishers = [];
$result = $connect->query("SELECT ID, Наименование FROM издательство");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $publishers[$row['ID']] = $row['Наименование'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $connect->real_escape_string(trim($_POST['title']));
    $publisher_id = intval($_POST['publisher_id']);
    $page_count = intval($_POST['page_count']);
    $year = intval($_POST['year']);
    $isbn = $connect->real_escape_string(trim($_POST['isbn']));
    $udk = $connect->real_escape_string(trim($_POST['udk']));
    $bbk = $connect->real_escape_string(trim($_POST['bbk']));
    $description = $connect->real_escape_string(trim($_POST['description']));

    // التحقق من صحة الناشر
    if (!array_key_exists($publisher_id, $publishers)) {
        $error = "Выбранное издательство не существует.";
    }

    // معالجة رفع الصورة
    $image = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = IMG_DIR . $image_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $image_name;
            } else {
                $error = "Ошибка при загрузке изображения.";
            }
        } else {
            $error = "Допустимые форматы изображений: JPG, JPEG, PNG, GIF.";
        }
    }

    if (!$error) {
        if (empty($title) || empty($publisher_id) || empty($page_count) || empty($year) || empty($isbn) || empty($udk) || empty($bbk)) {
            $error = "Пожалуйста, заполните все обязательные поля!";
        } else {
            $stmt = $connect->prepare("INSERT INTO Книга (Название, ID_издательства, Количество_страниц, Год_издания, ISBN, УДК, ББК, Описание, Изображение) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siissssss", $title, $publisher_id, $page_count, $year, $isbn, $udk, $bbk, $description, $image);

            if ($stmt->execute()) {
                $book_id = $stmt->insert_id;
                $connect->query("INSERT INTO Экземпляр_Книги (ID_книги, Количество_выдач) VALUES ('$book_id', 0)");
                $success = "Книга успешно добавлена!";
            } else {
                $error = "Ошибка при добавлении книги: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$connect->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить книгу</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="welcome-header">
            <h1>Центральная городская библиотека им. Н.А. Некрасова</h1>
            <p>Краснодар, ул. Красная, 87</p>
        </div>
        <div class="auth-box">
            <h2>Добавить книгу</h2>
            <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
            <?php if ($success) { echo "<p style='color: green;'>$success</p>"; } ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Название книги" required>
                <select name="publisher_id" required>
                    <option value="">Выберите издательство</option>
                    <?php foreach ($publishers as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="page_count" placeholder="Количество страниц" required>
                <input type="number" name="year" placeholder="Год издания" required>
                <input type="text" name="isbn" placeholder="ISBN" required>
                <input type="text" name="udk" placeholder="УДК" required>
                <input type="text" name="bbk" placeholder="ББК" required>
                <textarea name="description" placeholder="Описание" rows="4"></textarea>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Добавить</button>
            </form>
            <p><a href="/index.php" class="logout-btn" style="background: rgba(175, 116, 64, 0.9);">Вернуться</a></p>
        </div>
    </div>
</body>
</html>
