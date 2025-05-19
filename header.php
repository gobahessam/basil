<?php
session_start();
$isAdmin = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0;
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Гость';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Georgia&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/main.css">
    <style>
        header {
            background: linear-gradient(to bottom, rgb(255, 236, 214), rgb(255, 215, 83));
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .header-logo h1 {
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
            font-size: 24px;
            margin: 0;
        }
        .header-logo p {
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
            font-size: 14px;
            margin: 5px 0 0;
        }
        nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        nav a.button {
            font-family: Poppins, sans-serif;
            color: white;
            background: rgb(175, 116, 64);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        nav a.button:hover {
            background: rgb(132, 76, 27);
        }
        .search-form {
            display: flex;
            gap: 10px;
        }
        .search-form input {
            padding: 8px;
            border: 1px solid rgb(175, 116, 64);
            border-radius: 5px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            width: 180px;
        }
        .search-form button {
            padding: 8px 15px;
            background: rgb(175, 116, 64);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: Poppins, sans-serif;
        }
        .search-form button:hover {
            background: rgb(132, 76, 27);
        }
        .dropdown {
            position: relative;
        }
        .dropdown-toggle {
            font-family: Poppins, sans-serif;
            color: white;
            background: rgb(175, 116, 64);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .dropdown-toggle:hover {
            background: rgb(132, 76, 27);
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            top: 100%;
            right: 0;
            z-index: 100;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            color: rgb(132, 76, 27);
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            font-family: Poppins, sans-serif;
            font-size: 14px;
        }
        .dropdown-content a:hover {
            background: rgb(255, 236, 214);
        }
        .contact-info {
            text-align: center;
            margin-top: 10px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: rgb(132, 76, 27);
            font-size: 13px;
        }
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                align-items: flex-start;
            }
            nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                width: 100%;
            }
            .search-form {
                width: 100%;
            }
            .search-form input {
                width: 100%;
            }
            .dropdown-content {
                right: auto;
                left: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="header-top">
                <div class="header-logo">
                    <h1>Центральная городская библиотека им. Н.А. Некрасова</h1>
                    <p>Краснодар, ул. Красная, 87</p>
                </div>
                <nav>
                    <a href="/index.php" class="button">Книги</a>
                    <a href="/schedule/index.php" class="button">График работы</a>
                    <a href="/about/index.php" class="button">О библиотеке</a>
                    <form class="search-form" method="GET" action="/index.php">
                        <input type="text" name="search" placeholder="Поиск книг..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit">Поиск</button>
                    </form>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle">Привет, <?php echo $username; ?>!</a>
                        <div class="dropdown-content">
                            <?php if ($isAdmin): ?>
                                <a href="/admin_dashboard.php">Админпанель</a>
                            <?php endif; ?>
                            <a href="/profile.php">Личный кабинет</a>
                            <a href="/logout.php">Выйти</a>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="contact-info">
                <p>8 (861) 274-32-27 | <a href="mailto:krd.library@mail.ru" style="color: rgb(132, 76, 27); text-decoration: none;">krd.library@mail.ru</a></p>
            </div>
        </div>
    </header>