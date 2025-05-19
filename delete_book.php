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

$success = '';
$error = '';

// معالجة طلب الحذف
if (isset($_GET['delete_id'])) {
    $book_id = intval($_GET['delete_id']);

    // جلب اسم الصورة لحذفها من المجلد
    $image_query = $connect->query("SELECT Изображение FROM Книга WHERE ID = $book_id");
    if ($image_query && $image_query->num_rows > 0) {
        $image = $image_query->fetch_assoc()['Изображение'];
        if ($image !== 'default.jpg') {
            $image_path = IMG_DIR . $image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }

    // حذف السجلات المرتبطة بسبب العلاقات الخارجية
    $connect->query("DELETE FROM Автор_Книги WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Жанр_Книги WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Очередь WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Экземпляр_Книги WHERE ID_книги = $book_id");

    // حذف الكتاب من جدول Книга
    $delete_query = "DELETE FROM Книга WHERE ID = $book_id";
    if ($connect->query($delete_query) === TRUE) {
        $success = "Книга успешно удалена!";
    } else {
        $error = "Ошибка при удалении книги: " . $connect->error;
    }
}

// جلب قائمة الكتب
$books = [];
$query = "SELECT ID, Название, Изображение FROM Книга ORDER BY ID ASC";
$result = $connect->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить книгу</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth.css">
    <style>
        .book-list {
            max-width: 800px;
            margin: 20px auto;
        }
        .book-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .book-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
        }
        .delete-btn {
            background-color: #ff6347;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .delete-btn:hover {
            background-color: #d43f2a;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="welcome-header">
            <h1>Центральная городская библиотека им. Н.А. Некрасова</h1>
            <p>Краснодар, ул. Красная, 87</p>
        </div>
        <div class="auth-box">
            <h2>Удалить книгу</h2>
            <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
            <?php if ($success) { echo "<p style='color: green;'>$success</p>"; } ?>
            <div class="book-list">
                <?php if (empty($books)): ?>
                    <p>Книги отсутствуют.</p>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="book-item">
                            <div>
                                <img src="/img/<?php echo htmlspecialchars($book['Изображение']); ?>" alt="Обложка">
                                <span><?php echo htmlspecialchars($book['Название']); ?> (ID: <?php echo $book['ID']; ?>)</span>
                            </div>
                            <a href="delete_book.php?delete_id=<?php echo $book['ID']; ?>" class="delete-btn" onclick="return confirm('Вы уверены, что хотите удалить эту книгу?');">Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <p><a href="/index.php" class="logout-btn" style="background: rgba(175, 116, 64, 0.9);">Вернуться</a></p>
        </div>
    </div>
</body>
</html>