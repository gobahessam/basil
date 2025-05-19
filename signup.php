<?php
ob_start();
session_start();

define("SERVERNAME", "localhost");
define("DB_LOGIN", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "library1");

$connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);

if ($connect->connect_error) {
    die("Ошибка подключения: " . $connect->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // التحقق من وجود اسم المستخدم بالفعل
    $stmt = $connect->prepare("SELECT ID FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Имя пользователя уже занято!";
    } else {
        // إضافة المستخدم إلى جدول Users
        $stmt = $connect->prepare("INSERT INTO Users (Username, Password, IsAdmin) VALUES (?, ?, 0)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $user_id = $connect->insert_id;

        // إضافة بيانات المستخدم إلى جدول пользователь
        $stmt = $connect->prepare("INSERT INTO пользователь (ID, Имя, Телефон) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $name, $phone);
        if ($stmt->execute()) {
            $success = "Регистрация прошла успешно! Теперь вы можете <a href='login.php'>войти</a>.";
        } else {
            $error = "Ошибка при регистрации: " . $connect->error;
            // حذف المستخدم من جدول Users إذا فشل الإدخال في جدول пользователь
            $connect->query("DELETE FROM Users WHERE ID = $user_id");
        }
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
    <title>Регистрация в библиотеке</title>
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
            <h2>Регистрация в библиотеке</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Имя пользователя" required>
                <input type="text" name="name" placeholder="Полное имя" required>
                <input type="text" name="phone" placeholder="Телефон" required>
                <div style="position: relative;">
                    <input type="password" name="password" id="signup-password" placeholder="Пароль" required>
                    <button type="button" class="toggle-password"><i class="fas fa-eye-slash"></i></button>
                </div>
                <button type="submit">Зарегистрироваться</button>
            </form>
            <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
            <?php if ($success) { echo "<p class='success'>$success</p>"; } ?>
            <p>Уже есть аккаунт? <a href='login.php'>Войти</a></p>
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