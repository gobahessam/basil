<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: /profile.php");
    exit();
}

$loan_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "root", "library1");
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// تحديث تاريخ الاستحقاق
$new_due_date = date('Y-m-d H:i:s', strtotime('+30 days'));
$stmt = $conn->prepare("UPDATE выданная_книга SET Дата_и_время_сдачи = ? WHERE ID = ? AND ID_Пользователя = ?");
if ($stmt === false) {
    die("Ошибка подготовки запроса (обновление): " . $conn->error);
}
$stmt->bind_param("sii", $new_due_date, $loan_id, $user_id);
if ($stmt->execute() === false) {
    die("Ошибка выполнения запроса (обновление): " . $stmt->error);
}

// إضافة سجل تمديد إلى جدول продление_книги
$renew_date = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT ID_экземпляра_книги FROM выданная_книга WHERE ID = ?");
if ($stmt === false) {
    die("Ошибка подготовки запроса (получение экземпляра): " . $conn->error);
}
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$book_copy_id = $row['ID_экземпляра_книги'];

$stmt = $conn->prepare("INSERT INTO продление_книги (ID_экземпляра_книги, ID_Пользователя, Новая_дата_сдачи, Дата_продления) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    die("Ошибка подготовки запроса (продление): " . $conn->error);
}
$stmt->bind_param("iiss", $book_copy_id, $user_id, $new_due_date, $renew_date);
if ($stmt->execute() === false) {
    die("Ошибка выполнения запроса (продление): " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: /profile.php");
exit();
?>