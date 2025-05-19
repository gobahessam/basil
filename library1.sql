-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 18 2025 г., 11:32
-- Версия сервера: 5.7.29
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `library1`
--

-- --------------------------------------------------------

--
-- Структура таблицы `books`
--

CREATE TABLE `books` (
  `ID` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Author` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Year` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IsAdmin` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`ID`, `Username`, `Password`, `IsAdmin`) VALUES
(1, 'admin', '$2y$10$RlrPIeXltc2fnPMJ6IL9buWcqTQdSgUUVAeCl3def1/hWYFjDNQBC', 1),
(5, 'newessam1', '$2y$10$qJqSWoQI.tS.x094Pl/omOpheCX0K8eNejVwgFTbNj/dVonpH7GeC', 0),
(7, 'esam', '$2y$10$twmgL5LTPeCEsFXapESj.uBFvGF3zwXogtXhDIsAh4OJrg8zHeyPG', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `издательство`
--

CREATE TABLE `издательство` (
  `ID` int(11) NOT NULL,
  `Наименование` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `издательство`
--

INSERT INTO `издательство` (`ID`, `Наименование`) VALUES
(1, 'Эксмо'),
(2, '65'),
(3, '65');

-- --------------------------------------------------------

--
-- Структура таблицы `книга`
--

CREATE TABLE `книга` (
  `ID` int(11) NOT NULL,
  `Название` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_издательства` int(11) NOT NULL,
  `Количество_страниц` int(11) DEFAULT NULL,
  `Год_издания` int(11) DEFAULT NULL,
  `ISBN` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `УДК` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ББК` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Описание` text COLLATE utf8mb4_unicode_ci,
  `Изображение` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `книга`
--

INSERT INTO `книга` (`ID`, `Название`, `ID_издательства`, `Количество_страниц`, `Год_издания`, `ISBN`, `УДК`, `ББК`, `Описание`, `Изображение`) VALUES
(1, 'Пример книги', 1, 300, 2020, '123-456-789', 'УДК123', 'ББК456', 'Описание книги\nКнига рассказывает о приключениях.', '1.jpg'),
(2, 'Евгений Онегин', 1, 240, 1833, '978-5-04-088591-6', 'УДК821', 'ББК84', 'Роман в стихах о любви и судьбе.', '2.jpg'),
(3, 'Война и мир', 1, 1225, 1869, '978-5-389-02129-7', 'УДК821', 'ББК84', 'Эпопея о жизни в эпоху Наполеона.', '3.jpg'),
(4, 'Преступление и наказание', 1, 672, 1866, '978-5-389-02130-3', 'УДК821', 'ББК84', 'Роман о морали и искуплении.', '4.jpg'),
(5, 'Гарри Поттер и Философский камень', 1, 332, 1997, '978-5-353-02158-2', 'УДК823', 'ББК84', 'Первая книга о юном волшебнике.', '5.jpg'),
(6, 'Старик и море', 1, 128, 1952, '978-5-17-080115-2', 'УДК813', 'ББК84', 'Повесть о борьбе человека с природой.', '6.jpg'),
(7, 'Игра престолов', 1, 694, 1996, '978-5-17-088659-3', 'УДК823', 'ББК84', 'Эпическое фэнтези о борьбе за трон.', '7.jpg'),
(8, 'Убийство в Восточном экспрессе', 1, 256, 1934, '978-5-04-088592-3', 'УДК823', 'ББК84', 'Классический детектив.', '8.jpg'),
(9, 'Оно', 1, 1138, 1986, '978-5-17-092333-5', 'УДК813', 'ББК84', 'Ужасы о противостоянии злу.', '9.jpg'),
(10, 'Гордость и предубеждение', 1, 432, 1813, '978-5-389-02131-0', 'УДК823', 'ББК84', 'Роман о любви и социальных предрассудках.', '10.jpg'),
(11, 'Сказки Пушкина', 1, 160, 1834, '978-5-04-088593-0', 'УДК821', 'ББК84', 'Сборник сказок для детей и взрослых.', '1.jpg'),
(12, 'Евгений Онегин', 1, 240, 1833, '978-5-04-088591-6', 'УДК821', 'ББК84', 'Роман в стихах о любви и судьбе.', '2.jpg'),
(13, 'Война и мир', 1, 1225, 1869, '978-5-389-02129-7', 'УДК821', 'ББК84', 'Эпопея о жизни в эпоху Наполеона.', '3.jpg'),
(14, 'Преступление и наказание', 1, 672, 1866, '978-5-389-02130-3', 'УДК821', 'ББК84', 'Роман о морали и искуплении.', '4.jpg'),
(15, 'Гарри Поттер и Философский камень', 1, 332, 1997, '978-5-353-02158-2', 'УДК823', 'ББК84', 'Первая книга о юном волшебнике.', '5.jpg'),
(16, 'Старик и море', 1, 128, 1952, '978-5-17-080115-2', 'УДК813', 'ББК84', 'Повесть о борьбе человека с природой.', '6.jpg'),
(17, 'Игра престолов', 1, 694, 1996, '978-5-17-088659-3', 'УДК823', 'ББК84', 'Эпическое фэнтези о борьбе за трон.', '7.jpg'),
(18, 'Убийство в Восточном экспрессе', 1, 256, 1934, '978-5-04-088592-3', 'УДК823', 'ББК84', 'Классический детектив.', '8.jpg'),
(19, 'Оно', 1, 1138, 1986, '978-5-17-092333-5', 'УДК813', 'ББК84', 'Ужасы о противостоянии злу.', '9.jpg'),
(20, 'Гордость и предубеждение', 1, 432, 1813, '978-5-389-02131-0', 'УДК823', 'ББК84', 'Роман о любви и социальных предрассудках.', '10.jpg'),
(21, 'Сказки Пушкина', 1, 160, 1834, '978-5-04-088593-0', 'УДК821', 'ББК84', 'Сборник сказок для детей и взрослых.', '1.jpg'),
(27, 'Тайна старого замка', 1, 325, 2023, '978-5-04-987654-3', 'УДК820', 'ББК84', '\"Увлекательный детективный роман о расследовании загадочного исчезновения в старинном замке. Книга полна неожиданных поворотов и интриг.', '681e393034fcd_boocover.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `пользователь`
--

CREATE TABLE `пользователь` (
  `ID` int(11) NOT NULL,
  `Имя` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Отчество` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Фамилия` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `Телефон` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Электронная_почта` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `пользователь`
--

INSERT INTO `пользователь` (`ID`, `Имя`, `Отчество`, `Фамилия`, `Телефон`, `Электронная_почта`) VALUES
(1, 'Алексей', 'Петрович', 'Смирнов', '+79001234567', 'alexey.smirnov@example.com'),
(5, 'есам', NULL, '', '892846777955', NULL),
(7, 'есам', NULL, '', '892846777955', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `продление_книги`
--

CREATE TABLE `продление_книги` (
  `ID` int(11) NOT NULL,
  `ID_экземпляра_книги` int(11) NOT NULL,
  `ID_Пользователя` int(11) NOT NULL,
  `Новая_дата_сдачи` datetime NOT NULL,
  `Дата_продления` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `продление_книги`
--

INSERT INTO `продление_книги` (`ID`, `ID_экземпляра_книги`, `ID_Пользователя`, `Новая_дата_сдачи`, `Дата_продления`) VALUES
(1, 1, 1, '2025-04-30 10:00:00', '2025-04-14 10:00:00'),
(2, 34, 7, '2025-06-10 10:09:52', '2025-05-11 10:09:52'),
(3, 1, 1, '2025-06-10 10:13:00', '2025-05-11 10:13:00'),
(4, 1, 1, '2025-06-10 14:39:35', '2025-05-11 14:39:35'),
(5, 5, 1, '2025-06-10 14:39:36', '2025-05-11 14:39:36'),
(6, 2, 1, '2025-06-10 14:39:37', '2025-05-11 14:39:37'),
(7, 3, 1, '2025-06-10 14:39:37', '2025-05-11 14:39:37'),
(8, 34, 7, '2025-06-10 14:40:55', '2025-05-11 14:40:55'),
(9, 4, 7, '2025-06-10 14:40:55', '2025-05-11 14:40:55'),
(10, 11, 7, '2025-06-10 14:40:56', '2025-05-11 14:40:56'),
(11, 6, 7, '2025-06-10 14:40:56', '2025-05-11 14:40:56');

-- --------------------------------------------------------

--
-- Структура таблицы `админ_уведомления`
--

CREATE TABLE `админ_уведомления` (
  `ID` int(11) NOT NULL,
  `Сообщение` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Дата_и_время` datetime NOT NULL,
  `Прочитано` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `админ_уведомления`
--

INSERT INTO `админ_уведомления` (`ID`, `Сообщение`, `Дата_и_время`, `Прочитано`) VALUES
(1, 'Пользователь просрочил возврат книги', '2025-04-15 10:00:00', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `автор`
--

CREATE TABLE `автор` (
  `ID` int(11) NOT NULL,
  `Имя` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Отчество` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Фамилия` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `автор`
--

INSERT INTO `автор` (`ID`, `Имя`, `Отчество`, `Фамилия`) VALUES
(1, 'Иван', 'Иванович', 'Иванов'),
(2, 'Александр', 'Сергеевич', 'Пушкин'),
(3, 'Лев', 'Николаевич', 'Толстой'),
(4, 'Фёдор', 'Михайлович', 'Достоевский'),
(5, 'Джоан', 'Кэтлин', 'Роулинг'),
(6, 'Эрнест', 'Миллер', 'Хемингуэй'),
(7, 'Джордж', 'Реймонд', 'Мартин'),
(8, 'Агата', 'Мэри', 'Кристи'),
(9, 'Стивен', 'Эдвин', 'Кинг'),
(10, 'Джейн', '', 'Остин'),
(11, 'Александр', 'Сергеевич', 'Пушкин'),
(12, 'Лев', 'Николаевич', 'Толстой'),
(13, 'Фёдор', 'Михайлович', 'Достоевский'),
(14, 'Джоан', 'Кэтлин', 'Роулинг'),
(15, 'Эрнест', 'Миллер', 'Хемингуэй'),
(16, 'Джордж', 'Реймонд', 'Мартин'),
(17, 'Агата', 'Мэри', 'Кристи'),
(18, 'Стивен', 'Эдвин', 'Кинг'),
(19, 'Джейн', NULL, 'Остин');

-- --------------------------------------------------------

--
-- Структура таблицы `автор_книги`
--

CREATE TABLE `автор_книги` (
  `ID_книги` int(11) NOT NULL,
  `ID_автора` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `автор_книги`
--

INSERT INTO `автор_книги` (`ID_книги`, `ID_автора`) VALUES
(1, 1),
(2, 2),
(11, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10);

-- --------------------------------------------------------

--
-- Структура таблицы `готов_к_получению`
--

CREATE TABLE `готов_к_получению` (
  `ID` int(11) NOT NULL,
  `ID_экземпляра_книги` int(11) NOT NULL,
  `ID_Пользователя` int(11) NOT NULL,
  `Дата_и_время` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `готов_к_получению`
--

INSERT INTO `готов_к_получению` (`ID`, `ID_экземпляра_книги`, `ID_Пользователя`, `Дата_и_время`) VALUES
(1, 1, 1, '2025-04-15 10:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `выданная_книга`
--

CREATE TABLE `выданная_книга` (
  `ID` int(11) NOT NULL,
  `ID_экземпляра_книги` int(11) NOT NULL,
  `ID_Пользователя` int(11) NOT NULL,
  `Дата_и_время_выдачи` datetime NOT NULL,
  `Дата_и_время_сдачи` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `выданная_книга`
--

INSERT INTO `выданная_книга` (`ID`, `ID_экземпляра_книги`, `ID_Пользователя`, `Дата_и_время_выдачи`, `Дата_и_время_сдачи`) VALUES
(11, 3, 5, '2025-05-18 10:35:49', '2025-06-17 10:35:49'),
(12, 18, 5, '2025-05-18 11:25:23', '2025-06-17 11:25:23'),
(13, 1, 7, '2025-05-18 11:26:00', '2025-06-17 11:26:00');

-- --------------------------------------------------------

--
-- Структура таблицы `жанр`
--

CREATE TABLE `жанр` (
  `ID` int(11) NOT NULL,
  `Наименование` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `жанр`
--

INSERT INTO `жанр` (`ID`, `Наименование`) VALUES
(1, 'Фантастика'),
(2, 'классика'),
(3, 'драма'),
(4, 'фэнтези'),
(5, 'детектив'),
(6, 'ужасы'),
(7, 'романтика');

-- --------------------------------------------------------

--
-- Структура таблицы `жанр_книги`
--

CREATE TABLE `жанр_книги` (
  `ID_книги` int(11) NOT NULL,
  `ID_жанра` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `жанр_книги`
--

INSERT INTO `жанр_книги` (`ID_книги`, `ID_жанра`) VALUES
(1, 1),
(2, 2),
(3, 2),
(6, 2),
(11, 2),
(4, 3),
(5, 4),
(7, 4),
(8, 5),
(9, 6),
(10, 7);

-- --------------------------------------------------------

--
-- Структура таблицы `журнал_учета`
--

CREATE TABLE `журнал_учета` (
  `ID` int(11) NOT NULL,
  `ID_экземпляра_книги` int(11) NOT NULL,
  `ID_Пользователя` int(11) NOT NULL,
  `Дата_и_время_выдачи` datetime NOT NULL,
  `Дата_и_время_возврата` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `журнал_учета`
--

INSERT INTO `журнал_учета` (`ID`, `ID_экземпляра_книги`, `ID_Пользователя`, `Дата_и_время_выдачи`, `Дата_и_время_возврата`) VALUES
(1, 1, 1, '2025-03-01 10:00:00', '2025-03-15 10:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `очередь`
--

CREATE TABLE `очередь` (
  `ID` int(11) NOT NULL,
  `ID_книги` int(11) NOT NULL,
  `ID_пользователя` int(11) NOT NULL,
  `Дата_и_время` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `очередь`
--

INSERT INTO `очередь` (`ID`, `ID_книги`, `ID_пользователя`, `Дата_и_время`) VALUES
(1, 1, 1, '2025-04-10 10:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `черный_список`
--

CREATE TABLE `черный_список` (
  `ID` int(11) NOT NULL,
  `ID_Пользователя` int(11) NOT NULL,
  `Причина` text COLLATE utf8mb4_unicode_ci,
  `Дата_добавления` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `черный_список`
--

INSERT INTO `черный_список` (`ID`, `ID_Пользователя`, `Причина`, `Дата_добавления`) VALUES
(1, 1, 'Просрочка возврата книги', '2025-04-15 10:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `экземпляр_книги`
--

CREATE TABLE `экземпляр_книги` (
  `ID` int(11) NOT NULL,
  `ID_книги` int(11) NOT NULL,
  `Количество_выдач` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `экземпляр_книги`
--

INSERT INTO `экземпляр_книги` (`ID`, `ID_книги`, `Количество_выдач`) VALUES
(1, 1, 5),
(2, 2, 4),
(3, 2, 4),
(4, 2, 4),
(5, 2, 4),
(6, 3, 3),
(7, 3, 3),
(8, 3, 3),
(9, 4, 5),
(10, 4, 5),
(11, 4, 5),
(12, 4, 5),
(13, 4, 5),
(14, 5, 4),
(15, 5, 4),
(16, 5, 4),
(17, 5, 4),
(18, 6, 3),
(19, 6, 3),
(20, 6, 3),
(21, 7, 5),
(22, 7, 5),
(23, 7, 5),
(24, 7, 5),
(25, 7, 5),
(26, 8, 4),
(27, 8, 4),
(28, 8, 4),
(29, 8, 4),
(30, 9, 3),
(31, 9, 3),
(32, 9, 3),
(33, 10, 4),
(34, 10, 4),
(35, 10, 4),
(36, 10, 4),
(37, 11, 5),
(38, 11, 5),
(39, 11, 5),
(40, 11, 5),
(41, 11, 5),
(43, 27, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Индексы таблицы `издательство`
--
ALTER TABLE `издательство`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `книга`
--
ALTER TABLE `книга`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_издательства` (`ID_издательства`);

--
-- Индексы таблицы `пользователь`
--
ALTER TABLE `пользователь`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `продление_книги`
--
ALTER TABLE `продление_книги`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_экземпляра_книги` (`ID_экземпляра_книги`),
  ADD KEY `ID_Пользователя` (`ID_Пользователя`);

--
-- Индексы таблицы `админ_уведомления`
--
ALTER TABLE `админ_уведомления`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `автор`
--
ALTER TABLE `автор`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `автор_книги`
--
ALTER TABLE `автор_книги`
  ADD PRIMARY KEY (`ID_книги`,`ID_автора`),
  ADD KEY `ID_автора` (`ID_автора`);

--
-- Индексы таблицы `готов_к_получению`
--
ALTER TABLE `готов_к_получению`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_экземпляра_книги` (`ID_экземпляра_книги`),
  ADD KEY `ID_Пользователя` (`ID_Пользователя`);

--
-- Индексы таблицы `выданная_книга`
--
ALTER TABLE `выданная_книга`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_экземпляра_книги` (`ID_экземпляра_книги`),
  ADD KEY `ID_Пользователя` (`ID_Пользователя`);

--
-- Индексы таблицы `жанр`
--
ALTER TABLE `жанр`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `жанр_книги`
--
ALTER TABLE `жанр_книги`
  ADD PRIMARY KEY (`ID_книги`,`ID_жанра`),
  ADD KEY `ID_жанра` (`ID_жанра`);

--
-- Индексы таблицы `журнал_учета`
--
ALTER TABLE `журнал_учета`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_экземпляра_книги` (`ID_экземпляра_книги`),
  ADD KEY `ID_Пользователя` (`ID_Пользователя`);

--
-- Индексы таблицы `очередь`
--
ALTER TABLE `очередь`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_книги` (`ID_книги`),
  ADD KEY `ID_пользователя` (`ID_пользователя`);

--
-- Индексы таблицы `черный_список`
--
ALTER TABLE `черный_список`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Пользователя` (`ID_Пользователя`);

--
-- Индексы таблицы `экземпляр_книги`
--
ALTER TABLE `экземпляр_книги`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_книги` (`ID_книги`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `books`
--
ALTER TABLE `books`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `издательство`
--
ALTER TABLE `издательство`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `книга`
--
ALTER TABLE `книга`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT для таблицы `пользователь`
--
ALTER TABLE `пользователь`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `продление_книги`
--
ALTER TABLE `продление_книги`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `админ_уведомления`
--
ALTER TABLE `админ_уведомления`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `автор`
--
ALTER TABLE `автор`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `готов_к_получению`
--
ALTER TABLE `готов_к_получению`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `выданная_книга`
--
ALTER TABLE `выданная_книга`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `жанр`
--
ALTER TABLE `жанр`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `журнал_учета`
--
ALTER TABLE `журнал_учета`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `очередь`
--
ALTER TABLE `очередь`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `черный_список`
--
ALTER TABLE `черный_список`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `экземпляр_книги`
--
ALTER TABLE `экземпляр_книги`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `книга`
--
ALTER TABLE `книга`
  ADD CONSTRAINT `Книга_ibfk_1` FOREIGN KEY (`ID_издательства`) REFERENCES `издательство` (`ID`);

--
-- Ограничения внешнего ключа таблицы `продление_книги`
--
ALTER TABLE `продление_книги`
  ADD CONSTRAINT `Продление_Книги_ibfk_1` FOREIGN KEY (`ID_экземпляра_книги`) REFERENCES `экземпляр_книги` (`ID`),
  ADD CONSTRAINT `Продление_Книги_ibfk_2` FOREIGN KEY (`ID_Пользователя`) REFERENCES `пользователь` (`ID`);

--
-- Ограничения внешнего ключа таблицы `автор_книги`
--
ALTER TABLE `автор_книги`
  ADD CONSTRAINT `Автор_Книги_ibfk_1` FOREIGN KEY (`ID_книги`) REFERENCES `книга` (`ID`),
  ADD CONSTRAINT `Автор_Книги_ibfk_2` FOREIGN KEY (`ID_автора`) REFERENCES `автор` (`ID`);

--
-- Ограничения внешнего ключа таблицы `готов_к_получению`
--
ALTER TABLE `готов_к_получению`
  ADD CONSTRAINT `Готов_К_Получению_ibfk_1` FOREIGN KEY (`ID_экземпляра_книги`) REFERENCES `экземпляр_книги` (`ID`),
  ADD CONSTRAINT `Готов_К_Получению_ibfk_2` FOREIGN KEY (`ID_Пользователя`) REFERENCES `пользователь` (`ID`);

--
-- Ограничения внешнего ключа таблицы `выданная_книга`
--
ALTER TABLE `выданная_книга`
  ADD CONSTRAINT `Выданная_Книга_ibfk_1` FOREIGN KEY (`ID_экземпляра_книги`) REFERENCES `экземпляр_книги` (`ID`),
  ADD CONSTRAINT `Выданная_Книга_ibfk_2` FOREIGN KEY (`ID_Пользователя`) REFERENCES `пользователь` (`ID`);

--
-- Ограничения внешнего ключа таблицы `жанр_книги`
--
ALTER TABLE `жанр_книги`
  ADD CONSTRAINT `Жанр_Книги_ibfk_1` FOREIGN KEY (`ID_книги`) REFERENCES `книга` (`ID`),
  ADD CONSTRAINT `Жанр_Книги_ibfk_2` FOREIGN KEY (`ID_жанра`) REFERENCES `жанр` (`ID`);

--
-- Ограничения внешнего ключа таблицы `журнал_учета`
--
ALTER TABLE `журнал_учета`
  ADD CONSTRAINT `Журнал_Учета_ibfk_1` FOREIGN KEY (`ID_экземпляра_книги`) REFERENCES `экземпляр_книги` (`ID`),
  ADD CONSTRAINT `Журнал_Учета_ibfk_2` FOREIGN KEY (`ID_Пользователя`) REFERENCES `пользователь` (`ID`);

--
-- Ограничения внешнего ключа таблицы `очередь`
--
ALTER TABLE `очередь`
  ADD CONSTRAINT `Очередь_ibfk_1` FOREIGN KEY (`ID_книги`) REFERENCES `книга` (`ID`),
  ADD CONSTRAINT `Очередь_ibfk_2` FOREIGN KEY (`ID_пользователя`) REFERENCES `пользователь` (`ID`);

--
-- Ограничения внешнего ключа таблицы `черный_список`
--
ALTER TABLE `черный_список`
  ADD CONSTRAINT `Черный_Список_ibfk_1` FOREIGN KEY (`ID_Пользователя`) REFERENCES `пользователь` (`ID`);

--
-- Ограничения внешнего ключа таблицы `экземпляр_книги`
--
ALTER TABLE `экземпляр_книги`
  ADD CONSTRAINT `Экземпляр_Книги_ibfk_1` FOREIGN KEY (`ID_книги`) REFERENCES `книга` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
