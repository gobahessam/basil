<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['isAdmin'] != 1) {
    header("Location: /login.php");
    exit();
}

define("SERVERNAME", "localhost");
define("DB_LOGIN", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "library1");
define("IMG_DIR", "C:/OpenServer/domains/localhost/img/");

// تسجيل الأخطاء في ملف
ini_set('log_errors', 1);
ini_set('error_log', 'C:/OpenServer/domains/localhost/error_log.txt');

$connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);
if ($connect->connect_error) {
    error_log("Ошибка подключения к базе данных: " . $connect->connect_error);
    die("Ошибка подключения к базе данных: " . $connect->connect_error);
}

// Установка кодировки UTF-8 для поддержки русского текста
$connect->set_charset("utf8mb4");

$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = '';

// Получение статистики
$stats = [
    'users' => 0,
    'books' => 0,
    'loans' => 0
];
$result = $connect->query("SELECT COUNT(*) AS count FROM users");
if ($result) {
    $stats['users'] = $result->fetch_assoc()['count'];
} else {
    error_log("Ошибка при получении статистики пользователей: " . $connect->error);
}
$result = $connect->query("SELECT COUNT(*) AS count FROM Книга");
if ($result) {
    $stats['books'] = $result->fetch_assoc()['count'];
} else {
    error_log("Ошибка при получении статистики книг: " . $connect->error);
}
$result = $connect->query("SELECT COUNT(*) AS count FROM выданная_книга");
if ($result) {
    $stats['loans'] = $result->fetch_assoc()['count'];
} else {
    error_log("Ошибка при получении статистики выдач: " . $connect->error);
}

// Получение списка издательств
$publishers = [];
$result = $connect->query("SELECT ID, Наименование FROM издательство");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $publishers[$row['ID']] = $row['Наименование'];
    }
} else {
    error_log("Ошибка при получении издательств: " . $connect->error);
    $error = "Ошибка при получении издательств: " . $connect->error;
}

// Получение списка пользователей
$users = [];
$stmt = $connect->prepare("SELECT u.ID, u.Username, p.Имя AS name, p.Телефон AS phone 
                          FROM users u LEFT JOIN пользователь p ON u.ID = p.ID");
if ($stmt === false) {
    error_log("Ошибка подготовки запроса пользователей: " . $connect->error);
    $error = "Ошибка подготовки запроса пользователей: " . $connect->error;
    $result = $connect->query("SELECT u.ID, u.Username, p.Имя AS name, p.Телефон AS phone 
                              FROM users u LEFT JOIN пользователь p ON u.ID = p.ID");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        error_log("Ошибка альтернативного запроса пользователей: " . $connect->error);
        $error .= "Ошибка альтернативного запроса пользователей: " . $connect->error;
    }
} else {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}

// Получение списка книг
$books = [];
$result = $connect->query("SELECT ID, Название, Изображение FROM Книга ORDER BY ID ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
} else {
    error_log("Ошибка при получении книг: " . $connect->error);
    $error .= "Ошибка при получении книг: " . $connect->error;
}

// Получение списка доступных экземпляров
$available_copies = [];
$result = $connect->query("SELECT эк.ID, к.Название FROM экземпляр_книги эк 
                          JOIN Книга к ON эк.ID_книги = к.ID 
                          WHERE эк.ID NOT IN (SELECT ID_экземпляра_книги FROM выданная_книга)");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $available_copies[] = $row;
    }
} else {
    error_log("Ошибка при получении доступных экземпляров: " . $connect->error);
    $error .= "Ошибка при получении доступных экземпляров: " . $connect->error;
}

// Получение списка выдач
$loans = [];
$stmt = $connect->prepare("SELECT вк.ID, p.Имя AS user_name, к.Название AS book_title, 
                          вк.Дата_и_время_выдачи AS issue_date, вк.Дата_и_время_сдачи AS due_date 
                          FROM выданная_книга вк 
                          JOIN пользователь p ON вк.ID_Пользователя = p.ID 
                          JOIN экземпляр_книги эк ON вк.ID_экземпляра_книги = эк.ID 
                          JOIN Книга к ON эк.ID_книги = к.ID");
if ($stmt === false) {
    error_log("Ошибка подготовки запроса выдач: " . $connect->error);
    $error .= "Ошибка подготовки запроса выдач: " . $connect->error;
    $result = $connect->query("SELECT вк.ID, p.Имя AS user_name, к.Название AS book_title, 
                              вк.Дата_и_время_выдачи AS issue_date, вк.Дата_и_время_сдачи AS due_date 
                              FROM выданная_книга вк 
                              JOIN пользователь p ON вк.ID_Пользователя = p.ID 
                              JOIN экземпляр_книги эк ON вк.ID_экземпляра_книги = эк.ID 
                              JOIN Книга к ON эк.ID_книги = к.ID");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $loans[] = $row;
        }
    } else {
        error_log("Ошибка альтернативного запроса выдач: " . $connect->error);
        $error .= "Ошибка альтернативного запроса выдач: " . $connect->error;
    }
} else {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    $stmt->close();
}

// Обработка добавления пользователя
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $stmt = $connect->prepare("INSERT INTO users (Username, Password, IsAdmin) VALUES (?, ?, 0)");
    if ($stmt === false) {
        error_log("Ошибка подготовки добавления пользователя: " . $connect->error);
        $error = "Ошибка подготовки добавления пользователя: " . $connect->error;
    } else {
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $user_id = $connect->insert_id;
        $stmt->close();
        $stmt = $connect->prepare("INSERT INTO пользователь (ID, Имя, Телефон) VALUES (?, ?, ?)");
        if ($stmt === false) {
            error_log("Ошибка подготовки добавления данных пользователя: " . $connect->error);
            $error = "Ошибка подготовки добавления данных пользователя: " . $connect->error;
        } else {
            $stmt->bind_param("iss", $user_id, $name, $phone);
            $stmt->execute();
            $stmt->close();
            header("Location: /admin_dashboard.php?success=Пользователь успешно добавлен");
            exit();
        }
    }
}

// Обработка выдачи книги
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['issue_book'])) {
    $user_id = intval($_POST['user_id']);
    $book_copy_id = intval($_POST['book_copy_id']);
    $issue_date = date('Y-m-d H:i:s');
    $due_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = $connect->prepare("INSERT INTO выданная_книга (ID_Пользователя, ID_экземпляра_книги, Дата_и_время_выдачи, Дата_и_время_сдачи) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Ошибка подготовки выдачи книги: " . $connect->error);
        $error = "Ошибка подготовки выдачи книги: " . $connect->error;
    } else {
        $stmt->bind_param("iiss", $user_id, $book_copy_id, $issue_date, $due_date);
        $stmt->execute();
        $stmt->close();
        header("Location: /admin_dashboard.php?success=Книга успешно выдана");
        exit();
    }
}

// Обработка добавления книги
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = $connect->real_escape_string(trim($_POST['title']));
    $publisher_id = intval($_POST['publisher_id']);
    $page_count = intval($_POST['page_count']);
    $year = intval($_POST['year']);
    $isbn = $connect->real_escape_string(trim($_POST['isbn']));
    $udk = $connect->real_escape_string(trim($_POST['udk']));
    $bbk = $connect->real_escape_string(trim($_POST['bbk']));
    $description = $connect->real_escape_string(trim($_POST['description']));
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
        $stmt = $connect->prepare("INSERT INTO Книга (Название, ID_издательства, Количество_страниц, Год_издания, ISBN, УДК, ББК, Описание, Изображение) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Ошибка подготовки добавления книги: " . $connect->error);
            $error = "Ошибка подготовки добавления книги: " . $connect->error;
        } else {
            $stmt->bind_param("siissssss", $title, $publisher_id, $page_count, $year, $isbn, $udk, $bbk, $description, $image);
            if ($stmt->execute()) {
                $book_id = $stmt->insert_id;
                $connect->query("INSERT INTO Экземпляр_Книги (ID_книги, Количество_выдач) VALUES ('$book_id', 0)");
                header("Location: /admin_dashboard.php?success=Книга успешно добавлена");
                exit();
            } else {
                error_log("Ошибка при добавлении книги: " . $stmt->error);
                $error = "Ошибка при добавлении книги: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Обработка удаления книги
if (isset($_GET['delete_book'])) {
    $book_id = intval($_GET['delete_book']);
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
    $connect->query("DELETE FROM Автор_Книги WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Жанр_Книги WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Очередь WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Экземпляр_Книги WHERE ID_книги = $book_id");
    $connect->query("DELETE FROM Книга WHERE ID = $book_id");
    header("Location: /admin_dashboard.php?success=Книга успешно удалена");
    exit();
}

// Обработка удаления пользователя
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $connect->query("DELETE FROM выданная_книга WHERE ID_Пользователя = $user_id");
    $connect->query("DELETE FROM пользователь WHERE ID = $user_id");
    $connect->query("DELETE FROM users WHERE ID = $user_id");
    header("Location: /admin_dashboard.php?success=Пользователь успешно удален");
    exit();
}

// Обработка удаления выдачи
if (isset($_GET['delete_loan'])) {
    $loan_id = intval($_GET['delete_loan']);
    $connect->query("DELETE FROM выданная_книга WHERE ID = $loan_id");
    header("Location: /admin_dashboard.php?success=Запись о выдаче успешно удалена");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 30px auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .stats-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card.bg-brown-light {
            background: rgb(175, 116, 64);
        }
        .stats-card.bg-brown-dark {
            background: rgb(132, 76, 27);
        }
        .stats-card.bg-yellow-light {
            background: rgb(255, 215, 83);
            color: rgb(132, 76, 27);
        }
        .nav-tabs .nav-link {
            font-weight: 600;
            color: rgb(132, 76, 27);
            border: none;
            padding: 15px 25px;
            transition: background 0.3s ease;
        }
        .nav-tabs .nav-link.active {
            background: rgb(175, 116, 64);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .tab-content {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        .table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background: rgb(175, 116, 64);
            color: white;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid rgb(175, 116, 64);
        }
        .btn-primary {
            background: rgb(175, 116, 64);
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: rgb(132, 76, 27);
        }
        .btn-danger {
            border-radius: 8px;
        }
        .error {
            color: #dc3545;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .success {
            color: #28a745;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .book-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="dashboard-container">
            <h2 class="text-center mb-4">Панель администратора</h2>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card bg-brown-light fade-in">
                        <h4>Всего пользователей</h4>
                        <h2><?php echo $stats['users']; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-brown-dark fade-in">
                        <h4>Всего книг</h4>
                        <h2><?php echo $stats['books']; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-yellow-light fade-in">
                        <h4>Активные выдачи</h4>
                        <h2><?php echo $stats['loans']; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Вкладки -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#users">Управление пользователями</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#issue">Выдача книги</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#add_book">Добавить книгу</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#delete_book">Удалить книгу</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Управление пользователями -->
                <div class="tab-pane fade show active" id="users">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title">Добавить нового пользователя</h4>
                            <form method="POST">
                                <input type="hidden" name="add_user" value="1">
                                <div class="mb-3">
                                    <input type="text" name="username" class="form-control" placeholder="Имя пользователя" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="Имя" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="phone" class="form-control" placeholder="Телефон" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Добавить</button>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Список пользователей</h4>
                            <?php if (empty($users)): ?>
                                <p>Пользователи отсутствуют.</p>
                            <?php else: ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Имя пользователя</th>
                                            <th>Имя</th>
                                            <th>Телефон</th>
                                            <th>Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['ID']; ?></td>
                                                <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                                <td>
                                                    <a href="/admin_dashboard.php?delete_user=<?php echo $user['ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить пользователя?');">Удалить</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Выданные книги</h4>
                            <?php if (empty($loans)): ?>
                                <p>Нет выданных книг.</p>
                            <?php else: ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Пользователь</th>
                                            <th>Книга</th>
                                            <th>Дата выдачи</th>
                                            <th>Дата возврата</th>
                                            <th>Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loans as $loan): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($loan['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['issue_date']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                                                <td>
                                                    <a href="/admin_dashboard.php?delete_loan=<?php echo $loan['ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить запись о выдаче?');">Удалить</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Выдача книги -->
                <div class="tab-pane fade" id="issue">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Выдать книгу</h4>
                            <form method="POST">
                                <input type="hidden" name="issue_book" value="1">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Пользователь:</label>
                                    <select name="user_id" class="form-select" required>
                                        <option value="">Выберите пользователя</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['ID']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="book_copy_id" class="form-label">Книга:</label>
                                    <select name="book_copy_id" class="form-select" required>
                                        <option value="">Выберите книгу</option>
                                        <?php foreach ($available_copies as $copy): ?>
                                            <option value="<?php echo $copy['ID']; ?>" title="<?php echo htmlspecialchars($copy['Название']); ?>">
                                                <?php echo htmlspecialchars($copy['Название']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Выдать</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Добавление книги -->
                <div class="tab-pane fade" id="add_book">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Добавить новую книгу</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="add_book" value="1">
                                <div class="mb-3">
                                    <input type="text" name="title" class="form-control" placeholder="Название книги" required>
                                </div>
                                <div class="mb-3">
                                    <select name="publisher_id" class="form-select" required>
                                        <option value="">Выберите издательство</option>
                                        <?php foreach ($publishers as $id => $name): ?>
                                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <input type="number" name="page_count" class="form-control" placeholder="Количество страниц" required>
                                </div>
                                <div class="mb-3">
                                    <input type="number" name="year" class="form-control" placeholder="Год издания" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="isbn" class="form-control" placeholder="ISBN" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="udk" class="form-control" placeholder="УДК" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="bbk" class="form-control" placeholder="ББК" required>
                                </div>
                                <div class="mb-3">
                                    <textarea name="description" class="form-control" placeholder="Описание" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary">Добавить</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Удаление книги -->
                <div class="tab-pane fade" id="delete_book">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Удалить книгу</h4>
                            <?php if (empty($books)): ?>
                                <p>Книги отсутствуют.</p>
                            <?php else: ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Обложка</th>
                                            <th>Название</th>
                                            <th>ID</th>
                                            <th>Действие</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                            <tr>
                                                <td><img src="/img/<?php echo htmlspecialchars($book['Изображение']); ?>" alt="Обложка" class="book-img"></td>
                                                <td><?php echo htmlspecialchars($book['Название']); ?></td>
                                                <td><?php echo $book['ID']; ?></td>
                                                <td>
                                                    <a href="/admin_dashboard.php?delete_book=<?php echo $book['ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить книгу?');">Удалить</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="text-center py-3">
        <p>© <?php echo date('Y'); ?> Электронная библиотека</p>
    </footer>
    <script>
        $(document).ready(function() {
            $('.nav-tabs a').on('shown.bs.tab', function() {
                $('.tab-pane').addClass('fade-in');
                setTimeout(() => $('.tab-pane').removeClass('fade-in'), 500);
            });
        });
    </script>
</body>
</html>
<?php $connect->close(); ?>