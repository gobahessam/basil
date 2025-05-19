<?php
ob_start();
session_start();

// تسجيل الخروج إذا تم النقر على زر الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    ob_end_clean();
    header("Location: login.php");
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

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // استرجاع بيانات المستخدم من جدول Users
    $stmt = $connect->prepare("SELECT ID, Username, Password, IsAdmin FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // التحقق من كلمة المرور
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['isAdmin'] = $user['IsAdmin'];

            // استرجاع بيانات المستخدم من جدول пользователь
            $user_id = $user['ID'];
            $stmt = $connect->prepare("SELECT Имя AS name, Телефон AS phone FROM пользователь WHERE ID = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_info = $stmt->get_result()->fetch_assoc();

            $_SESSION['name'] = $user_info['name'] ?? $user['Username'];
            $_SESSION['phone'] = $user_info['phone'] ?? '';

            ob_end_clean();
            header("Location: index.php");
            exit();
        } else {
            $error = "Неверный пароль или имя пользователя!";
        }
    } else {
        $error = "Неверный пароль или имя пользователя!";
    }
    $stmt->close();
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в библиотеке</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="welcome-header">
            <h1>Центральная городская библиотека им. Н.А. Некрасова</h1>
            <p>Краснодар, ул. Красная, 87</p>
        </div>
        <div class="auth-box">
            <h2>Вход в библиотеку</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Имя пользователя" value="<?php echo htmlspecialchars($username); ?>" required>
                <div style="position: relative;">
                    <input type="password" name="password" id="login-password" placeholder="Пароль" required>
                    <button type="button" class="toggle-password"><i class="fas fa-eye-slash"></i></button>
                </div>
                <button type="submit">Войти</button>
            </form>
            <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
            <p>Нет аккаунта? <a href="signup.php">Зарегистрироваться</a></p>
        </div>
    </div>
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordField = this.previousElementSibling;
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                } else {
                    passwordField.type = "password";
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                }
            });
        });
    </script>
</body>
</html>