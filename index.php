<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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

$isAdmin = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0;
$search_query = isset($_GET['search']) && !empty(trim($_GET['search'])) ? htmlspecialchars(trim($_GET['search'])) : '';

// استرجاع عدد الكتب المستعارة
$loaned_books_count = 0;
$loaned_books_query = $connect->query("SELECT COUNT(*) AS total FROM выданная_книга");
if ($loaned_books_query) {
    $loaned_books_count = $loaned_books_query->fetch_assoc()['total'];
}

// استرجاع الكتب مع البحث
$books = [];
$where_clause = $search_query ? "WHERE к.Название LIKE '%$search_query%'" : "";
$books_query = $connect->query("SELECT к.ID, к.Название, к.Изображение, GROUP_CONCAT(LOWER(ж.Наименование) SEPARATOR '; ') AS Жанры 
                               FROM книга к 
                               LEFT JOIN жанр_книги жк ON к.ID = жк.ID_книги 
                               LEFT JOIN жанр ж ON жк.ID_жанра = ж.ID 
                               $where_clause 
                               GROUP BY к.ID");
if ($books_query) {
    while ($row = $books_query->fetch_assoc()) {
        $books[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотека</title>
    <link rel="stylesheet" href="/main.css">
    <style>
        .books-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .book-item {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 200px;
            padding: 15px;
            text-align: center;
        }
        .book-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
        .book-item h3 {
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
            font-size: 18px;
            margin: 10px 0;
        }
        .book-item p {
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
            font-size: 14px;
        }
        .search-form {
            text-align: center;
            margin: 20px 0;
        }
        .search-form input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-form button {
            padding: 10px 20px;
            background: rgb(175, 116, 64);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .loaned-books {
            text-align: center;
            margin: 20px 0;
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'header.php'; ?>
        <main>
            <div class="loaned-books">
                <h3>Количество выданных книг в библиотеке: <?php echo $loaned_books_count; ?></h3>
            </div>
            <div class="search-form">
                <form method="GET">
                    <input type="text" name="search" placeholder="Поиск книг..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Поиск</button>
                </form>
            </div>
            <div class="books-container">
                <?php if (empty($books)): ?>
                    <p>Книги не найдены.</p>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="book-item">
                            <a href="/book/index.php?id=<?php echo $book['ID']; ?>">
                                <img src="/img/<?php echo htmlspecialchars($book['Изображение']); ?>" alt="Обложка">
                                <h3><?php echo htmlspecialchars($book['Название']); ?></h3>
                                <p><?php echo htmlspecialchars($book['Жанры'] ?: 'Без жанра'); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
        <footer></footer>
    </div>
</body>
</html>
<?php $connect->close(); ?>