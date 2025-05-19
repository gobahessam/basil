<?php
session_start();

// التحقق مما إذا كان المستخدم قد سجل الدخول بالفعل، إذا كان كذلك يتم توجيهه إلى الصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $connect->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // البحث عن المستخدم في قاعدة البيانات
    $query = "SELECT ID, Password FROM Users WHERE Username = '$username'";
    $result = $connect->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // التحقق من كلمة المرور
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['ID'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Неверный пароль!";
        }
    } else {
        $error = "Пользователь не найден!";
    }
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в библиотеку</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: rgb(255, 236, 214);
        }
        .login-box {
            background-color: rgb(255, 241, 190);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
            text-align: center;
            width: 300px;
        }
        .login-box h2 {
            color: rgb(132, 76, 27);
            font-family: Georgia, 'Times New Roman', Times, serif;
            margin-bottom: 20px;
        }
        .login-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #90592d;
            border-radius: 5px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-size: 16px;
        }
        .login-box button {
            background-color: #ffc471;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-size: 16px;
            color: rgb(132, 76, 27);
            cursor: pointer;
            transition-duration: 0.5s;
        }
        .login-box button:hover {
            background-color: rgb(175, 116, 64);
            color: white;
        }
        .error {
            color: red;
            font-family: Georgia, 'Times New Roman', Times, serif;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Вход в библиотеку</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Имя пользователя" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit">Войти</button>
            </form>
            <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
        </div>
    </div>
</body>
</html>