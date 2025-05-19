<?php
session_start();

// التحقق مما إذا كان المستخدم مشرفًا
if (!isset($_SESSION['user_id']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: /login.php");
    exit();
}

$isAdmin = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0;

define("SERVERNAME", "localhost");
define("DB_LOGIN", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "library1");

$connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);

// ضبط الترميز لدعم الأحرف غير اللاتينية
if (!$connect->set_charset("utf8mb4")) {
    die("Ошибка установки кодировки: " . $connect->error);
}

if ($connect->connect_error) {
    die("Ошибка подключения: " . $connect->connect_error);
}

// معالجة إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));

    // إضافة إلى جدول users
    $stmt = $connect->prepare("INSERT INTO users (Username, Password, IsAdmin) VALUES (?, ?, 0)");
    if ($stmt === false) {
        die("Ошибка подготовки запроса (добавление пользователя): " . $connect->error);
    }
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute() === false) {
        die("Ошибка выполнения запросا (добавление пользователя): " . $stmt->error);
    }
    $user_id = $connect->insert_id;

    // إضافة إلى جدول пользователь
    $stmt = $connect->prepare("INSERT INTO пользователь (ID, Имя, Телефон) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Ошибка подготовки запроса (добавление данных пользователя): " . $connect->error);
    }
    $stmt->bind_param("iss", $user_id, $name, $phone);
    if ($stmt->execute() === false) {
        die("Ошибка выполнения запроса (добавление данных пользователя): " . $stmt->error);
    }

    header("Location: /admin.php?success=Пользователь добавлен");
    exit();
}

// معالجة حذف مستخدم
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);

    $stmt = $connect->prepare("DELETE FROM выданная_книга WHERE ID_Пользователя = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $stmt = $connect->prepare("DELETE FROM пользователь WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $stmt = $connect->prepare("DELETE FROM users WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    header("Location: /admin.php?success=Пользователь удален");
    exit();
}

// معالجة حذف سجل إعارة
if (isset($_GET['delete_loan'])) {
    $loan_id = intval($_GET['delete_loan']);
    $stmt = $connect->prepare("DELETE FROM выданная_книга WHERE ID = ?");
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();

    header("Location: /admin.php?success=Запись о выдаче удалена");
    exit();
}

// استرجاع بيانات المستخدمين
$users = [];
$stmt = $connect->prepare("SELECT u.ID, u.Username, u.IsAdmin, p.Имя AS name, p.Телефон AS phone 
                          FROM users u 
                          LEFT JOIN пользователь p ON u.ID = p.ID");
if ($stmt === false) {
    die("Ошибка подготовки запроса (пользователи): " . $connect->error);
}
$stmt->execute();
$result = $stmt->get_result();
if ($result === false) {
    die("Ошибка получения результата (пользователи): " . $stmt->error);
}
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// استرجاع بيانات الكتب المقترضة
$loans = [];
$stmt = $connect->prepare("SELECT вк.ID, p.Имя AS user_name, k.Название AS book_title, вк.Дата_и_время_выдачи AS issue_date, вк.Дата_и_время_сдачи AS due_date 
                          FROM выданная_книга вк 
                          JOIN пользователь p ON вк.ID_Пользователя = p.ID 
                          JOIN экземпляр_книги эк ON вк.ID_экземпляра_книги = эк.ID 
                          JOIN книга k ON эк.ID_книги = k.ID");
if ($stmt === false) {
    die("Ошибка подготовки запроса (выданные книги): " . $connect->error . " (SQL: " . $connect->error . ")");
}
$stmt->execute();
$result = $stmt->get_result();
if ($result === false) {
    die("Ошибка получения результата (выданные книги): " . $stmt->error);
}
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}
?>

<?php include 'header.php'; ?>

<main>
    <h2>Админпанель</h2>

    <?php if (isset($_GET['success'])) { ?>
        <p style="color: green; margin-bottom: 15px;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php } ?>

    <!-- قسم إضافة مستخدم جديد -->
    <section style="margin-bottom: 30px;">
        <h3>Добавить нового пользователя</h3>
        <form method="POST" style="display: flex; flex-direction: column; gap: 10px; max-width: 400px;">
            <input type="hidden" name="add_user" value="1">
            <input type="text" name="username" placeholder="Имя пользователя" required style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <input type="password" name="password" placeholder="Пароль" required style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <input type="text" name="name" placeholder="Имя" required style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <input type="text" name="phone" placeholder="Телефон" required style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <button type="submit" style="background: #af7440; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Добавить</button>
        </form>
    </section>

    <!-- قسم بيانات المستخدمين -->
    <section style="margin-bottom: 30px;">
        <h3>Список пользователей</h3>
        <?php if (empty($users)) { ?>
            <p>Пользователи отсутствуют.</p>
        <?php } else { ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: rgba(175, 116, 64, 0.1);">
                        <th style="border: 1px solid #ccc; padding: 10px;">ID</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Имя пользователя</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Имя</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Телефон</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo $user['ID']; ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($user['Username']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;">
                                <a href="admin.php?delete_user=<?php echo $user['ID']; ?>" style="color: red;" onclick="return confirm('Вы уверены, что хотите удалить пользователя?');">Удалить</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </section>

    <!-- قسم الكتب المقترضة -->
    <section>
        <h3>Выданные книги</h3>
        <?php if (empty($loans)) { ?>
            <p>Нет выданных книг.</p>
        <?php } else { ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: rgba(175, 116, 64, 0.1);">
                        <th style="border: 1px solid #ccc; padding: 10px;">Пользователь</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Книга</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Дата выдачи</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Дата возврата</th>
                        <th style="border: 1px solid #ccc; padding: 10px;">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan) { ?>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['user_name']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['book_title']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['issue_date']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;"><?php echo htmlspecialchars($loan['due_date']); ?></td>
                            <td style="border: 1px solid #ccc; padding: 10px;">
                                <a href="admin.php?delete_loan=<?php echo $loan['ID']; ?>" style="color: red;" onclick="return confirm('Вы уверены, что хотите удалить запись о выдаче?');">Удалить</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </section>
</main>
<footer style="text-align: center; padding: 20px; background-color: #af7440; color: white; margin-top: 20px;">
    <p>© 2025 Библиотека</p>
</footer>
</body>
</html>
<?php $connect->close(); ?>