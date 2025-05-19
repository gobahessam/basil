<?php
session_start();

// التحقق مما إذا كان المستخدم قد سجل الدخول
if (!isset($_SESSION['user_id'])) {
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
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

    header("Location: /profile.php");
    exit();
}
?>

<?php include 'header.php'; ?>

<main>
    <h2>Оформить книгу</h2>
    <form method="POST">
        <label for="book_copy_id">Выберите книгу:</label>
        <select name="book_copy_id" id="book_copy_id" required>
            <option value="">-- Выберите книгу --</option>
            <?php foreach ($books as $book) { ?>
                <option value="<?php echo $book['ID']; ?>"><?php echo htmlspecialchars($book['Название']); ?></option>
            <?php } ?>
        </select>
        <button type="submit" style="background: #af7440; color: white; padding: 8px 16px; border: none; border-radius: 15px; cursor: pointer;">Оформить</button>
    </form>
</main>
<footer>
</footer>
</body>
</html>
<?php $connect->close(); ?>