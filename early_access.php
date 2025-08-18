<?php

require_once 'config.php';

$message = '';
$isSubmitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    // Простая валидация email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // 2. СОЕДИНЕНИЕ С БАЗОЙ ДАННЫХ
        $conn = new mysqli($servername, $username, $password, $dbname, $dbstatport);

        if ($conn->connect_error) {
            error_log("Ошибка подключения к базе данных для раннего доступа: " . $conn->connect_error);
            $message = "An error occurred while connecting to the database. Please try again later.";
        } else {
            // 3. СОХРАНЕНИЕ EMAIL
            // Убедитесь, что у вас есть таблица `early_access_emails` с колонкой `email`
            // Например: CREATE TABLE early_access_emails (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
            $sql = "INSERT INTO early_access_emails (email) VALUES (?) ON DUPLICATE KEY UPDATE created_at=NOW()";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $message = "Thank you, you're on the list!";
                    $isSubmitted = true;
                } else {
                    $message = "An error occurred while saving the email: " . $stmt->error;
                    error_log("Ошибка при сохранении email в early_access_emails: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $message = "Error preparing the query: " . $conn->error;
                error_log("Ошибка подготовки запроса для early_access_emails: " . $conn->error);
            }
            $conn->close();
        }
    } else {
        $message = "Please enter a valid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Early Access</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column; /* Изменено для вертикального размещения элементов */
            justify-content: flex-start; /* Выравнивание элементов к началу */
            align-items: center; /* Центрирование по горизонтали */
            min-height: 100vh;
            background-color: #443932; /* Новый цвет фона */
            margin: 0;
            background-image: url('bg.jpg'); /* Фон */
            background-size: auto 100vh; /* Помещается по высоте, ширина пропорционально */
            background-position: center center; /* Посередине по оси X */
            background-repeat: no-repeat;
            overflow-y: auto; /* Позволяет прокрутку, если контент не помещается */
        }
        .container {
            background-color: rgba(255, 255, 255, 0); /* Абсолютно прозрачный фон */
            padding: 20px; /* Уменьшен padding */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90vw; /* Адаптивная ширина */
            max-width: 400px; /* Максимальная ширина для десктопов */
            -webkit-backdrop-filter: blur(20px); /* Сильное размытие */
            backdrop-filter: blur(20px);
            margin-top: 15px; /* Уменьшаем отступ сверху, чтобы поднять окно */
            margin-bottom: auto; /* Отталкивается от низа, центрируя */
        }

        /* Медиа-запросы для десктопов */
        @media (min-width: 768px) {
            .container {
                max-width: 600px; /* Увеличиваем максимальную ширину для десктопов */
            }
        }

        /* Стили для изображений */
        .top-image {
            width: 50vw; /* Адаптивная ширина */
            max-width: 250px; /* Максимальная ширина */
            height: auto;
            display: block;
            margin: 20px auto 10px auto; /* Отступы: сверху 20px, авто по бокам, снизу 10px */
        }
        .app-icon {
            width: 50vw; /* Чуть больше адаптивная ширина */
            max-width: 260px; /* Чуть больше максимальная ширина */
            height: auto;
            display: block;
            margin: 10px auto 0 auto; /* Отступы: сверху 10px, авто по бокам, снизу 0 */
        }
        h1 {
            color: #fff; /* Белый цвет для заголовка */
            font-size: clamp(24px, 8vw, 36px); /* Адаптивный размер шрифта с min/max */
            margin-bottom: 15px;
        }
        p {
            color: #fff; /* Белый цвет для параграфов */
            margin-bottom: 10px; /* Уменьшен отступ */
            font-size: clamp(14px, 4vw, 18px); /* Адаптивный размер шрифта с min/max */
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #fff; /* Белый цвет для меток */
            text-align: left;
            font-size: clamp(16px, 4.5vw, 20px); /* Адаптивный размер шрифта с min/max */
        }
        input[type="email"] {
            width: calc(100% - 20px); /* Учитываем padding */
            padding: 12px; /* Увеличен padding */
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: clamp(16px, 4.5vw, 20px); /* Адаптивный размер шрифта с min/max */
        }
        button {
            background-color: #ffe066; /* Яркий желтый цвет */
            color: #333; /* Темный текст для контраста */
            padding: 15px 30px; /* Увеличен padding */
            border: none;
            border-radius: 8px; /* Немного больше скругление */
            cursor: pointer;
            font-size: clamp(18px, 5vw, 24px); /* Адаптивный размер шрифта с min/max */
            transition: background-color 0.3s ease;
            font-weight: bold; /* Жирный шрифт */
            margin-top: 20px; /* Отступ сверху */
        }
        button:hover {
            background-color: #ffcc00; /* Более темный желтый при наведении */
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: clamp(14px, 4vw, 18px); /* Адаптивный размер шрифта с min/max */
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <img src="number.png" alt="Road To Legend Logo" class="top-image">
    <img src="appicon_big.png" alt="App Icon" class="app-icon">
    <div class="container">
        <h1>Early Access</h1>
        <?php if ($message): ?>
            <p class="message <?php echo $isSubmitted ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!$isSubmitted): ?>
            <p>Android in development.<br>Be first — get exclusive bonuses!</p>
            <form action="early_access.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit">Subscribe</button>
            </form>
        <?php else: ?>
            <p>We will notify you as soon as the game is available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
