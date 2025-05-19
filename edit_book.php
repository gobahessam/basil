<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: /login.php");
    exit();
}

define("SERVERNAME", "localhost");
define("DB_LOGIN", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "library1");

$connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);
if ($connect->connect_error) {
    die("Ошибка подключения: " . $connect->connect_error);
}
$connect->set_charset("utf8mb4");

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$success = '';
$book = [];
$selected_authors = [];
$selected_genres = [];
$publishers = [];
$authors = [];
$genres = [];

// استرجاع تفاصيل الكتاب
if ($book_id > 0) {
    $book_query = $connect->query("SELECT Название, ID_издательства, Количество_страниц, Год_издания, ISBN, УДК, ББК, Описание, Изображение 
                                  FROM Книга WHERE ID = $book_id");
    if ($book_query && $book_query->num_rows > 0) {
        $book = $book_query->fetch_assoc();
        $selected_authors = $connect->query("SELECT ID_автора FROM Автор_Книги WHERE ID_книги = $book_id")->fetch_all(MYSQLI_ASSOC);
        $selected_authors = array_column($selected_authors, 'ID_автора');
        $selected_genres = $connect->query("SELECT ID_жанра FROM Жанр_Книги WHERE ID_книги = $book_id")->fetch_all(MYSQLI_ASSOC);
        $selected_genres = array_column($selected_genres, 'ID_жанра');
        $book['Экземпляры'] = $connect->query("SELECT COUNT(*) AS count FROM Экземпляр_Книги WHERE ID_книги = $book_id")->fetch_assoc()['count'];
    } else {
        $errors[] = "Книга не найдена.";
    }
}

// استرجاع الناشرين، المؤلفين، والأجناس
$publishers = $connect->query("SELECT ID, Наименование FROM Издательство ORDER BY Наименование")->fetch_all(MYSQLI_ASSOC);
$authors = $connect->query("SELECT ID, CONCAT(Имя, ' ', Отчество, ' ', Фамилия) AS Имя FROM Автор ORDER BY Фамилия")->fetch_all(MYSQLI_ASSOC);
$genres = $connect->query("SELECT ID, Наименование FROM Жанр ORDER BY Наименование")->fetch_all(MYSQLI_ASSOC);

// معالجة النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $publisher_id = intval($_POST['publisher_id'] ?? 0);
    $pages = intval($_POST['pages'] ?? 0);
    $year = intval($_POST['year'] ?? 0);
    $isbn = trim($_POST['isbn'] ?? '');
    $udk = trim($_POST['udk'] ?? '');
    $bbk = trim($_POST['bbk'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $copies = intval($_POST['copies'] ?? 0);
    $authors = isset($_POST['authors']) ? array_map('intval', $_POST['authors']) : [];
    $genres = isset($_POST['genres']) ? array_map('intval', $_POST['genres']) : [];
    
    // التحقق من الحقول
    if (empty($title)) $errors[] = "Название книги обязательно.";
    if ($publisher_id <= 0) $errors[] = "Выберите издательство.";
    if ($pages <= 0) $errors[] = "Укажите корректное количество страниц.";
    if ($year <= 0 || $year > date('Y')) $errors[] = "Укажите корректный год издания.";
    if ($copies < 0) $errors[] = "Количество экземпляров не может быть отрицательным.";
    
    // معالجة الصورة
    $image = $book['Изображение'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Допустимы только файлы JPEG или PNG.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Размер файла не должен превышать 5 МБ.";
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = "book_$book_id." . $ext;
            $upload_path = "C:/OpenServer/domains/localhost/img/$image";
            if ($book['Изображение'] !== 'default.jpg' && file_exists("C:/OpenServer/domains/localhost/img/" . $book['Изображение'])) {
                unlink("C:/OpenServer/domains/localhost/img/" . $book['Изображение']);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Ошибка при загрузке изображения.";
            }
        }
    }
    
    if (empty($errors)) {
        // تحديث الكتاب
        $stmt = $connect->prepare("UPDATE Книга SET Название = ?, ID_издательства = ?, Количество_страниц = ?, Год_издания = ?, ISBN = ?, УДК = ?, ББК = ?, Описание = ?, Изображение = ? WHERE ID = ?");
        $stmt->bind_param("siissssssi", $title, $publisher_id, $pages, $year, $isbn, $udk, $bbk, $description, $image, $book_id);
        if ($stmt->execute()) {
            // تحديث المؤلفين
            $connect->query("DELETE FROM Автор_Книги WHERE ID_книги = $book_id");
            foreach ($authors as $author_id) {
                $stmt = $connect->prepare("INSERT INTO Автор_Книги (ID_книги, ID_автора) VALUES (?, ?)");
                $stmt->bind_param("ii", $book_id, $author_id);
                $stmt->execute();
                $stmt->close();
            }
            // تحديث الأجناس
            $connect->query("DELETE FROM Жанр_Книги WHERE ID_книги = $book_id");
            foreach ($genres as $genre_id) {
                $stmt = $connect->prepare("INSERT INTO Жанр_Книги (ID_книги, ID_жанра) VALUES (?, ?)");
                $stmt->bind_param("ii", $book_id, $genre_id);
                $stmt->execute();
                $stmt->close();
            }
            // تحديث عدد النسخ
            $current_copies = $book['Экземпляры'];
            if ($copies > $current_copies) {
                for ($i = $current_copies + 1; $i <= $copies; $i++) {
                    $connect->query("INSERT INTO Экземпляр_Книги (ID_книги) VALUES ($book_id)");
                }
            } elseif ($copies < $current_copies) {
                $excess = $current_copies - $copies;
                $connect->query("DELETE FROM Экземпляр_Книги WHERE ID_книги = $book_id AND ID NOT IN (SELECT ID_экземпляра_книги FROM выданная_книга) LIMIT $excess");
            }
            $success = "Книга успешно обновлена.";
        } else {
            $errors[] = "Ошибка при обновлении книги: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать книгу</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f1e9;
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            color: rgb(132, 76, 27);
        }
        .form-label {
            font-weight: bold;
            color: rgb(132, 76, 27);
        }
        .form-control, .form-select {
            border: 2px solid #e0c7a0;
            border-radius: 8px;
            padding: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgb(175, 116, 64);
            box-shadow: 0 0 5px rgba(175, 116, 64, 0.5);
        }
        .btn-primary {
            background-color: rgb(175, 116, 64);
            border-color: rgb(175, 116, 64);
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-primary:hover {
            background-color: rgb(132, 76, 27);
            border-color: rgb(132, 76, 27);
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        @media (max-width: 576px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Редактировать книгу</h1>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <?php if (!empty($book)): ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-label">Название книги</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($book['Название']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="publisher_id" class="form-label">Издательство</label>
                    <select class="form-select" id="publisher_id" name="publisher_id" required>
                        <option value="">Выберите издательство</option>
                        <?php foreach ($publishers as $publisher): ?>
                            <option value="<?php echo $publisher['ID']; ?>" <?php echo $publisher['ID'] == $book['ID_издательства'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($publisher['Наименование']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="authors" class="form-label">Авторы</label>
                    <select class="form-select" id="authors" name="authors[]" multiple size="5">
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo $author['ID']; ?>" <?php echo in_array($author['ID'], $selected_authors) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['Имя']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="genres" class="form-label">Жанры</label>
                    <select class="form-select" id="genres" name="genres[]" multiple size="5">
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo $genre['ID']; ?>" <?php echo in_array($genre['ID'], $selected_genres) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre['Наименование']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pages" class="form-label">Количество страниц</label>
                    <input type="number" class="form-control" id="pages" name="pages" value="<?php echo htmlspecialchars($book['Количество_страниц']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="year" class="form-label">Год издания</label>
                    <input type="number" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($book['Год_издания']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['ISBN']); ?>">
                </div>
                <div class="form-group">
                    <label for="udk" class="form-label">УДК</label>
                    <input type="text" class="form-control" id="udk" name="udk" value="<?php echo htmlspecialchars($book['УДК']); ?>">
                </div>
                <div class="form-group">
                    <label for="bbk" class="form-label">ББК</label>
                    <input type="text" class="form-control" id="bbk" name="bbk" value="<?php echo htmlspecialchars($book['ББК']); ?>">
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Описание</label>
                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($book['Описание']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image" class="form-label">Изображение книги</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png">
                    <small class="form-text text-muted">Текущее изображение: <?php echo htmlspecialchars($book['Изображение']); ?></small>
                </div>
                <div class="form-group">
                    <label for="copies" class="form-label">Количество экземпляров</label>
                    <input type="number" class="form-control" id="copies" name="copies" value="<?php echo htmlspecialchars($book['Экземпляры']); ?>" min="0" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Сохранить изменения</button>
            </form>
        <?php else: ?>
            <p class="text-danger text-center">Книга не найдена.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $connect->close(); ?>