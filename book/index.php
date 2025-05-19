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
$connect->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['isAdmin'] ?? 0;
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

$book = $connect->query("SELECT Название, И.Наименование AS Издательство, Количество_страниц, Год_издания, ISBN, УДК, ББК, Описание, Изображение 
                         FROM Книга К JOIN Издательство И ON К.ID_издательства = И.ID 
                         WHERE К.ID = $book_id")->fetch_assoc();
if ($book) {
    $book['Автор'] = $connect->query("SELECT GROUP_CONCAT(CONCAT(LEFT(Имя, 1), '.', LEFT(Отчество, 1), '. ', Фамилия)) AS Авторы 
                                     FROM Автор JOIN Автор_Книги ON Автор.ID = Автор_Книги.ID_автора 
                                     WHERE Автор_Книги.ID_книги = $book_id")->fetch_assoc()['Авторы'];
    $book['Жанр'] = $connect->query("SELECT GROUP_CONCAT(LOWER(Наименование) SEPARATOR '; ') AS Жанры 
                                    FROM Жанр JOIN Жанр_Книги ON Жанр.ID = Жанр_Книги.ID_жанра 
                                    WHERE Жанр_Книги.ID_книги = $book_id")->fetch_assoc()['Жанры'];
    // Handle missing or invalid image
    $image_path = "C:/OpenServer/domains/localhost/img/" . $book['Изображение'];
    if (empty($book['Изображение']) || !file_exists($image_path)) {
        $book['Изображение'] = 'default.jpg';
    }
}

$available_copies = 0;
$copies_query = $connect->query("SELECT COUNT(*) AS available 
                                FROM экземпляр_книги эк 
                                WHERE эк.ID_книги = $book_id 
                                AND эк.ID NOT IN (SELECT ID_экземпляра_книги FROM выданная_книга)");
if ($copies_query) {
    $available_copies = $copies_query->fetch_assoc()['available'];
}

$has_borrowed = false;
if (!$is_admin) {
    $result = $connect->query("SELECT COUNT(*) AS count 
                              FROM выданная_книга вк 
                              JOIN экземпляр_книги эк ON вк.ID_экземпляра_книги = эк.ID 
                              WHERE вк.ID_Пользователя = $user_id AND эк.ID_книги = $book_id");
    if ($result) {
        $has_borrowed = $result->fetch_assoc()['count'] > 0;
    }
}

$in_queue = false;
if (!$is_admin) {
    $result = $connect->query("SELECT COUNT(*) AS count 
                              FROM Очередь 
                              WHERE ID_пользователя = $user_id AND ID_книги = $book_id");
    if ($result) {
        $in_queue = $result->fetch_assoc()['count'] > 0;
    }
}

$queue = [];
$result = $connect->query("SELECT CONCAT(П.Имя, ' ', П.Отчество, ' ', LEFT(П.Фамилия, 1), '.') AS Пользователь, 
                                  DATE_FORMAT(О.Дата_и_время, '%d.%m.%Y %H:%i') AS Дата_и_время, О.ID AS QueueID 
                           FROM Очередь О JOIN Пользователь П/lo ON О.ID_пользователя = П.ID 
                           WHERE О.ID_книги = $book_id 
                           ORDER BY О.ID");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $queue[] = $row;
    }
}

$borrowers = [];
if ($is_admin) {
    $result = $connect->query("SELECT CONCAT(П.Имя, ' ', П.Отчество, ' ', LEFT(П.Фамилия, 1), '.') AS Пользователь, 
                                      DATE_FORMAT(вк.Дата_и_время_выдачи, '%d.%m.%Y %H:%i') AS Дата_выдачи, 
                                      DATE_FORMAT(вк.Дата_и_время_сдачи, '%d.%m.%Y %H:%i') AS Дата_сдачи, вк.ID AS LoanID 
                               FROM выданная_книга вк 
                               JOIN Пользователь П ON вк.ID_Пользователя = П.ID 
                               JOIN экземпляр_книги эк ON вк.ID_экземпляра_книги = эк.ID 
                               WHERE эк.ID_книги = $book_id");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $borrowers[] = $row;
        }
    }
}

if (!$is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_book'])) {
    if ($has_borrowed) {
        $error = "Вы уже взяли эту книгу.";
    } elseif ($in_queue) {
        $error = "Вы уже в очереди на эту книгу.";
    } elseif ($available_copies > 0) {
        $copy_result = $connect->query("SELECT эк.ID 
                                      FROM экземпляр_книги эк 
                                      WHERE эк.ID_книги = $book_id 
                                      AND эк.ID NOT IN (SELECT ID_экземпляра_книги FROM выданная_книга) 
                                      LIMIT 1");
        if ($copy_result && $copy_result->num_rows > 0) {
            $copy_id = $copy_result->fetch_assoc()['ID'];
            $issue_date = date('Y-m-d H:i:s');
            $due_date = date('Y-m-d H:i:s', strtotime('+30 days'));
            $stmt = $connect->prepare("INSERT INTO выданная_книга (ID_Пользователя, ID_экземпляра_книги, Дата_и_время_выдачи, Дата_и_время_сдачи) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $copy_id, $issue_date, $due_date);
            if ($stmt->execute()) {
                $success = "Книга успешно взята!";
                $available_copies--;
                $has_borrowed = true;
            } else {
                $error = "Ошибка при взятии книги: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Нет доступных экземпляров.";
        }
    }
}

if (!$is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_queue'])) {
    if ($has_borrowed) {
        $error = "Вы уже взяли эту книгу.";
    } elseif ($in_queue) {
        $error = "Вы уже в очереди на эту книгу.";
    } else {
        $queue_date = date('Y-m-d H:i:s');
        $stmt = $connect->prepare("INSERT INTO Очередь (ID_пользователя, ID_книги, Дата_и_время) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $book_id, $queue_date);
        if ($stmt->execute()) {
            $success = "Вы успешно добавлены в очередь!";
            $in_queue = true;
            // Fetch updated queue
            $queue = [];
            $result = $connect->query("SELECT CONCAT(П.Имя, ' ', П.Отчество, ' ', LEFT(П.Фамилия, 1), '.') AS Пользователь, 
                                              DATE_FORMAT(О.Дата_и_время, '%d.%m.%Y %H:%i') AS Дата_и_время, О.ID AS QueueID 
                                       FROM Очередь О JOIN Пользователь П ON О.ID_пользователя = П.ID 
                                       WHERE О.ID_книги = $book_id 
                                       ORDER BY О.ID");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $queue[] = $row;
                }
            }
        } else {
            $error = "Ошибка при добавлении в очередь: " . $stmt->error;
        }
        $stmt->close();
    }
}

if ($is_admin && isset($_GET['action'])) {
    if ($_GET['action'] == 'delete_book') {
        $image_query = $connect->query("SELECT Изображение FROM Книга WHERE ID = $book_id");
        if ($image_query && $image_query->num_rows > 0) {
            $image = $image_query->fetch_assoc()['Изображение'];
            if ($image !== 'default.jpg') {
                $image_path = "C:/OpenServer/domains/localhost/img/" . $image;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        $connect->query("DELETE FROM Автор_Книги WHERE ID_книги = $book_id");
        $connect->query("DELETE FROM Жанр_Книги WHERE ID_книги = $book_id");
        $connect->query("DELETE FROM Очередь WHERE ID_книги = $book_id");
        $connect->query("DELETE FROM Экземпляр_Книги WHERE ID_книги = $book_id");
        $connect->query("DELETE FROM Книга WHERE ID = $book_id");
        header("Location: /books.php?success=Книга успешно удалена");
        exit();
    } elseif ($_GET['action'] == 'delete_queue' && isset($_GET['queue_id'])) {
        $queue_id = intval($_GET['queue_id']);
        $connect->query("DELETE FROM Очередь WHERE ID = $queue_id");
        header("Location: /book/index.php?id=$book_id&success=Пользователь удален из очереди");
        exit();
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
    <link rel="stylesheet" href="/book/book.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <main>
            <?php if ($error): ?>
                <div class="alert alert-danger modern-alert fade-in"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($book): ?>
                <div class="book-card fade-in">
                    <div class="book-image">
                        <img id="book_img" src="/img/<?php echo htmlspecialchars($book['Изображение']); ?>" alt="<?php echo htmlspecialchars($book['Название']); ?>" onerror="this.src='/img/default.jpg';">
                    </div>
                    <div class="book-details">
                        <h1 class="book-title"><?php echo htmlspecialchars($book['Название']); ?></h1>
                        <div class="book-info">
                            <p><strong>Автор:</strong> <?php echo htmlspecialchars($book['Автор'] ?: 'Неизвестный автор'); ?></p>
                            <p><strong>Жанр:</strong> <?php echo htmlspecialchars($book['Жанр'] ?: 'Без жанра'); ?></p>
                            <p><strong>Издательство:</strong> <?php echo htmlspecialchars($book['Издательство']); ?></p>
                            <p><strong>Количество страниц:</strong> <?php echo htmlspecialchars($book['Количество_страниц']); ?></p>
                            <p><strong>Год издания:</strong> <?php echo htmlspecialchars($book['Год_издания']); ?></p>
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['ISBN']); ?></p>
                            <p><strong>УДК:</strong> <?php echo htmlspecialchars($book['УДК']); ?></p>
                            <p><strong>ББК:</strong> <?php echo htmlspecialchars($book['ББК']); ?></p>
                            <p class="copies-count"><strong>Доступно экземпляров:</strong> <span id="available_copies"><?php echo $available_copies; ?></span></p>
                        </div>
                        <?php if (!$is_admin): ?>
                            <div class="user-actions">
                                <?php if ($available_copies > 0 && !$has_borrowed && !$in_queue): ?>
                                    <form id="borrow_form" method="POST">
                                        <input type="hidden" name="borrow_book" value="1">
                                        <button type="submit" class="btn btn-primary modern-btn"><i class="fas fa-book"></i> Взять книгу</button>
                                    </form>
                                <?php elseif (!$has_borrowed && !$in_queue): ?>
                                    <form id="queue_form" method="POST">
                                        <input type="hidden" name="join_queue" value="1">
                                        <button type="submit" class="btn btn-secondary modern-btn"><i class="fas fa-clock"></i> Встать в очередь</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($is_admin): ?>
                            <div class="admin-actions">
                                <a href="/book/index.php?id=<?php echo $book_id; ?>&action=delete_book" class="btn btn-danger btn-sm modern-btn" onclick="return confirm('Вы уверены, что хотите удалить книгу?');"><i class="fas fa-trash"></i> Удалить книгу</a>
                                <a href="/edit_book.php?id=<?php echo $book_id; ?>" class="btn btn-primary btn-sm modern-btn"><i class="fas fa-edit"></i> Редактировать книгу</a>
                            </div>
                        <?php endif; ?>
                        <div class="book-description">
                            <h2>Описание</h2>
                            <p><?php echo str_replace("\n", "</p><p>", htmlspecialchars($book['Описание'])); ?></p>
                        </div>
                    </div>
                </div>
                <div class="additional-info">
                    <div class="queue-info fade-in">
                        <h2>Очередь</h2>
                        <table id="queue_table" class="modern-table">
                            <thead>
                                <tr>
                                    <th>Место в очереди</th>
                                    <th>Пользователь</th>
                                    <th>Дата и время записи</th>
                                    <?php if ($is_admin): ?>
                                        <th>Действие</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queue as $k => $v): ?>
                                    <tr>
                                        <td><?php echo $k + 1; ?></td>
                                        <td><?php echo htmlspecialchars($v['Пользователь']); ?></td>
                                        <td><?php echo htmlspecialchars($v['Дата_и_время']); ?></td>
                                        <?php if ($is_admin): ?>
                                            <td>
                                                <a href="/book/index.php?id=<?php echo $book_id; ?>&action=delete_queue&queue_id=<?php echo $v['QueueID']; ?>" class="btn btn-danger btn-sm modern-btn" onclick="return confirm('Вы уверены, что хотите удалить пользователя из очереди?');"><i class="fas fa-trash"></i> Удалить</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($is_admin && !empty($borrowers)): ?>
                        <div class="borrowers-info fade-in">
                            <h2>Текущие выдачи</h2>
                            <table id="borrowers_table" class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Пользователь</th>
                                        <th>Дата выдачи</th>
                                        <th>Дата сдачи</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($borrowers as $borrower): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($borrower['Пользователь']); ?></td>
                                            <td><?php echo htmlspecialchars($borrower['Дата_выдачи']); ?></td>
                                            <td><?php echo htmlspecialchars($borrower['Дата_сдачи']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="error modern-error fade-in">Книга не найдена.</p>
            <?php endif; ?>
        </main>
        <footer></footer>
    </div>

    <!-- Bootstrap Modal for Success Message -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Успех</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modal_message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary modern-btn" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle borrow form submission
        document.getElementById('borrow_form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/book/index.php?id=<?php echo $book_id; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const error = doc.querySelector('.alert-danger')?.textContent;
                const success = '<?php echo $success; ?>';
                if (success) {
                    document.getElementById('modal_message').textContent = success;
                    new bootstrap.Modal(document.getElementById('successModal')).show();
                    const copiesSpan = document.getElementById('available_copies');
                    const currentCopies = parseInt(copiesSpan.textContent);
                    if (currentCopies > 0) {
                        copiesSpan.textContent = currentCopies - 1;
                    }
                    document.querySelector('.user-actions').innerHTML = '';
                } else if (error) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger modern-alert fade-in';
                    alertDiv.textContent = error;
                    document.querySelector('main').prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 5000);
                }
            });
        });

        // Handle queue form submission with dynamic queue update
        document.getElementById('queue_form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/book/index.php?id=<?php echo $book_id; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const error = doc.querySelector('.alert-danger')?.textContent;
                const success = '<?php echo $success; ?>';
                if (success) {
                    document.getElementById('modal_message').textContent = success;
                    new bootstrap.Modal(document.getElementById('successModal')).show();
                    document.querySelector('.user-actions').innerHTML = '';
                    // Update queue table dynamically
                    fetch('/book/index.php?id=<?php echo $book_id; ?>')
                        .then(response => response.text())
                        .then(data => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(data, 'text/html');
                            const newQueueTable = doc.querySelector('#queue_table tbody');
                            document.querySelector('#queue_table tbody').innerHTML = newQueueTable.innerHTML;
                        });
                } else if (error) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger modern-alert fade-in';
                    alertDiv.textContent = error;
                    document.querySelector('main').prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 5000);
                }
            });
        });
    </script>
</body>
</html>
<?php $connect->close(); ?>