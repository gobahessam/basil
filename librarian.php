<?php
session_start();

// التحقق مما إذا كان المستخدم مشرفًا
if (!isset($_SESSION['user_id']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: /login.php");
    exit();
}

$isAdmin = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0;
$search_query = isset($_GET['search']) && !empty(trim($_GET['search'])) ? htmlspecialchars(trim($_GET['search'])) : '';

define("SERVERNAME", "localhost");
define("DB_LOGIN", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "library1");

$connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);

if ($connect->connect_error) {
    die("Ошибка подключения: " . $connect->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $book_copy_id = intval($_POST['book_copy_id']);
    $issue_date = date('Y-m-d H:i:s');
    $due_date = date('Y-m-d H:i:s', strtotime('+30 days'));

    $stmt = $connect->prepare("INSERT INTO выданная_книга (ID_Пользователя, ID_экземпляра_книги, Дата_и_время_выдачи, Дата_и_время_сдачи) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        die("Ошибка подготовки запроса (выдача книги): " . $connect->error);
    }
    $stmt->bind_param("iiss", $user_id, $book_copy_id, $issue_date, $due_date);
    if ($stmt->execute() === false) {
        die("Ошибка выполнения запроса (выдача книги): " . $stmt->error);
    }
    $stmt->close();

    header("Location: /librarian.php?success=Книга успешно выдана");
    exit();
}

// استرجاع قائمة المستخدمين
$users_query = $connect->query("SELECT ID, Имя AS name FROM пользователь");
if ($users_query === false) {
    die("Ошибка выполнения запроса (пользователи): " . $connect->error);
}
$users = [];
while ($row = $users_query->fetch_assoc()) {
    $users[] = $row;
}

// استرجاع قائمة النسخ المتاحة للكتب
$books_query = $connect->query("SELECT эк.ID, к.Название 
                               FROM экземпляр_книги эк 
                               JOIN книга к ON эк.ID_книги = к.ID 
                               WHERE эк.ID NOT IN (SELECT ID_экземпляра_книги FROM выданная_книга)");
if ($books_query === false) {
    die("Ошибка выполнения запроса (книги): " . $connect->error);
}
$books = [];
while ($row = $books_query->fetch_assoc()) {
    $books[] = $row;
}
?>

<?php include 'header.php'; ?>

<main>
    <h2>Панель библиотекаря</h2>
    <?php if (isset($_GET['success'])) { ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php } ?>
    <h3>Выдать книгу</h3>
    <form method="POST">
        <label for="user_id">Выберите пользователя:</label>
        <select name="user_id" id="user_id" required>
            <option value="">-- Выберите пользователя --</option>
            <?php foreach ($users as $user) { ?>
                <option value="<?php echo $user['ID']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
            <?php } ?>
        </select>
        <label for="book_copy_id">Выберите книгу:</label>
        <select name="book_copy_id" id="book_copy_id" required>
            <option value="">-- Выберите книгу --</option>
            <?php foreach ($books as $book) { ?>
                <option value="<?php echo $book['ID']; ?>"><?php echo htmlspecialchars($book['Название']); ?></option>
            <?php } ?>
        </select>
        <button type="submit" style="background: #af7440; color: white; padding: 8px 16px; border: none; border-radius: 15px; cursor: pointer;">Выдать</button>
    </form>
</main>
<footer>
</footer>
</body>
</html>
<?php $connect->close(); ?>