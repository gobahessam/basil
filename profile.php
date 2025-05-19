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

$user_id = $_SESSION['user_id'];

// استرجاع بيانات المستخدم من جدول пользователь مع التحقق من الأخطاء
$user = ['name' => $_SESSION['name'] ?? $_SESSION['username'], 'phone' => $_SESSION['phone'] ?? ''];
$stmt = $connect->prepare("SELECT Имя AS name, Телефон AS phone FROM пользователь WHERE ID = ?");
if ($stmt === false) {
    die("Ошибка подготовки запроса (пользователь): " . $connect->error);
}
$stmt->bind_param("i", $user_id);
if ($stmt->execute() === false) {
    die("Ошибка выполнения запроса (пользователь): " . $stmt->error);
}
$result = $stmt->get_result();
if ($result === false) {
    die("Ошибка получения результата (пользователь): " . $stmt->error);
}
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

// استرجاع الكتب المقترضة مع التحقق من الأخطاء (بدون شرط status)
$loans = [];
$stmt = $connect->prepare("SELECT вк.ID, к.Название, вк.Дата_и_время_выдачи AS issue_date, вк.Дата_и_время_сдачи AS due_date 
                          FROM выданная_книга вк 
                          JOIN экземпляр_книги эк ON вк.ID_экземпляра_книги = эк.ID 
                          JOIN книга к ON эк.ID_книги = к.ID 
                          WHERE вк.ID_Пользователя = ?");
if ($stmt === false) {
    die("Ошибка подготовки запроса (книги): " . $connect->error);
}
$stmt->bind_param("i", $user_id);
if ($stmt->execute() === false) {
    die("Ошибка выполнения запроса (книги): " . $stmt->error);
}
$result = $stmt->get_result();
if ($result === false) {
    die("Ошибка получения результата (книги): " . $stmt->error);
}
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}
?>

<?php include 'header.php'; ?>

<main>
    <h2>Личный кабинет</h2>
    <div class="user-info">
        <h3>Данные пользователя</h3>
        <p><strong>Имя:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Телефон:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
    </div>
    <div class="loans">
        <h3>Ваши книги</h3>
        <?php if (empty($loans)) { ?>
            <p>У вас нет взятых книг.</p>
        <?php } else { ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: rgba(175, 116, 64, 0.1);">
                        <th style="border: 1px solid #ccc; padding: 10px;">Книга</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Дата выдачи</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Дата возврата</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan) { ?>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['Название']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['issue_date']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['due_date']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;">
                                <a href="renew.php?id=<?php echo $loan['ID']; ?>" style="color: #af7440;">Продлить</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
        <p><a href="register_book.php" style="color: #af7440;">Оформить новую книгу</a></p>
    </div>
</main>
<footer>
</footer>
</body>
</html>
<?php $connect->close(); ?>