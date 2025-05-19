<?php
session_start();

// التحقق مما إذا كان المستخدم قد سجل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$isAdmin = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0;
$search_query = isset($_GET['search']) && !empty(trim($_GET['search'])) ? htmlspecialchars(trim($_GET['search'])) : '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотека</title>
    <link rel="stylesheet" href="/main.css">
    <link rel="stylesheet" href="schedule.css">
</head>
<body>
    <div class="container">
    <?php include '../header.php'; ?>

        <main>
            <h1>Расписание работы библиотеки</h1>
            <table id="schedule">
                <tr>
                    <th>День недели</th>
                    <th>Часы работы</th>
                </tr>
                <?php
                $daysOfWeek = ['понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'];
                $currentTime = date('H:i');
                $currentDay = date('N');
                foreach ($daysOfWeek as $key => $day) {
                    if ($currentDay == $key + 1) {
                        echo '<tr id="today">';
                    } else {
                        echo '<tr>';
                    }
                    echo '<td>' . $day . '</td>';
                    switch ($key) {
                        case 0: echo '<td>10:00 - 20:00</td>'; break;
                        case 1: echo '<td>выходной</td>'; break;
                        case 2: case 3: case 4: echo '<td>10:00 - 20:00</td>'; break;
                        case 5: case 6: echo '<td>10:00 - 18:00</td>'; break;
                        }
                    echo '</tr>'; } ?>
            </table>
        </main>
        <footer>
        </footer>
    </div>
</body>
</html>

