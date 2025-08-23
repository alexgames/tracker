<?php

require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. КОНФИГУРАЦИЯ URL-АДРЕСОВ
// Укажите URL для перенаправления пользователей
$androidStoreUrl = "https://play.google.com/store/apps/details?id=com.yourgame";
$iosStoreUrl = "https://apps.apple.com/ru/app/cardesign/id6478308548";
$earlyAccessFormUrl = "early_access.php"; // Используем относительный путь
$defaultUrl = "https://cardesign.app/";

// 3. ПОЛУЧЕНИЕ ДАННЫХ ИЗ ЗАПРОСА
// Получаем код из URL, переданный через .htaccess
$linkCode = $_GET['code'] ?? 'undefined';

// 4. ПРОВЕРКА СООТВЕТСТВИЯ ШАБЛОНУ И РАННИЙ ВЫХОД
// Если ссылка НЕ соответствует шаблону - немедленный редирект на URL раннего доступа
if (! (strpos($linkCode, 'tt') === 0 || 
    strpos($linkCode, 'yt') === 0 || 
    strpos($linkCode, 'ig') === 0 ||
    strpos($linkCode, 'tw') === 0 || 
    strpos($linkCode, 'ld') === 0 ||
    strpos($linkCode, 'dc') === 0 
)) {
    header("Location: " . $earlyAccessFormUrl);
    exit();
}

// сслки на кампании
if (strpos($linkCode, 'yt') === 0) {
    $iosStoreUrl = "https://apps.apple.com/app/apple-store/id6478308548?pt=95910874&ct=ytsh&mt=8";
} else if (strpos($linkCode, 'ig') === 0) {
    $iosStoreUrl = "https://apps.apple.com/app/apple-store/id6478308548?pt=95910874&ct=igh&mt=8";
} else if (strpos($linkCode, 'tw') === 0) {
    $iosStoreUrl = "https://apps.apple.com/app/apple-store/id6478308548?pt=95910874&ct=tw&mt=8";
} else if (strpos($linkCode, 'ld') === 0) {
    $iosStoreUrl = "https://apps.apple.com/app/apple-store/id6478308548?pt=95910874&ct=ld&mt=8";
} else if (strpos($linkCode, 'dc') === 0) {
    $iosStoreUrl = "https://apps.apple.com/app/apple-store/id6478308548?pt=95910874&ct=dc&mt=8";
}


// КОД НИЖЕ ВЫПОЛНЯЕТСЯ ТОЛЬКО ЕСЛИ ССЫЛКА СООТВЕТСТВУЕТ ШАБЛОНУ

// 5. ОПРЕДЕЛЕНИЕ ПЛАТФОРМЫ
// Получаем HTTP User-Agent для определения платформы
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$platform = 'other';
if (strpos($userAgent, 'Android') !== false) {
    $platform = 'android';
} elseif (strpos($userAgent, 'iPad') !== false) {
    $platform = 'ipad';
} elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPod') !== false) {
    $platform = 'iphone';
}

// 6. СОЕДИНЕНИЕ С БАЗОЙ ДАННЫХ
$conn = new mysqli($servername, $username, $password, $dbname, $dbstatport);

if ($conn->connect_error) {
    // В реальном проекте, лучше записывать ошибку в лог, а не выводить на экран
    // Для данной задачи, если не удалось подключиться, все равно перенаправляем на дефолтный URL
    error_log("Ошибка подключения к базе данных: " . $conn->connect_error);
    header("Location: " . $defaultUrl);
    exit();
}

// 7. СОХРАНЕНИЕ ДАННЫХ О КЛИКЕ
// Используем подготовленные выражения для предотвращения SQL-инъекций
$sql = "INSERT INTO clicks (link_code, platform, timestamp) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $linkCode, $platform);
$stmt->execute();

$stmt->close();
$conn->close();

// 8. ЛОГИКА ПЕРЕНАПРАВЛЕНИЯ (С ОПРЕДЕЛЕНИЕМ ПЛАТФОРМЫ)
$redirectUrl = $defaultUrl;

// Если платформа 'other' (компьютер) - перенаправляем на форму раннего доступа
if ($platform === 'other') {
    $redirectUrl = $earlyAccessFormUrl;
} else {
    // Логика для Android
    if ($platform === 'android') {
        // Если версия для Android еще не готова, перенаправляем на форму
        // Вы можете изменить 'true' на реальную проверку, когда игра будет готова
        $isAndroidAppReady = false;
        if ($isAndroidAppReady) {
            $redirectUrl = $androidStoreUrl;
        } else {
            $redirectUrl = $earlyAccessFormUrl;
        }
    }
    // Логика для iOS (iPhone и iPad)
    elseif ($platform === 'iphone') {
        $redirectUrl = $iosStoreUrl;
    } elseif ($platform === 'ipad') {
        // Используем отдельный URL, если есть версия для iPad
        $redirectUrl = $iosStoreUrl;
    }
}

// 9. ПЕРЕНАПРАВЛЕНИЕ
header("Location: " . $redirectUrl);
exit();

?>