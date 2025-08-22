<?php

require_once 'config.php';

// 1. СОЕДИНЕНИЕ С БАЗОЙ ДАННЫХ
$conn = new mysqli($servername, $username, $password, $dbname, $dbstatport);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// 2. ФУНКЦИИ ДЛЯ ПОЛУЧЕНИЯ ДАННЫХ

function getTotalClicksByPrefix($conn, $prefix) {
    $sql = "SELECT COUNT(*) FROM clicks WHERE link_code LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchPrefix = $prefix . '%';
    $stmt->bind_param("s", $searchPrefix);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $stmt->close();
    return $row[0];
}

function getHeaderClicks($conn, $headerCode) {
    $sql = "SELECT COUNT(*) FROM clicks WHERE link_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $headerCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $stmt->close();
    return $row[0];
}

function getPlatformClicks($conn, $platform) {
    $sql = "SELECT COUNT(*) FROM clicks WHERE platform = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $platform);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $stmt->close();
    return $row[0];
}

function getTotalEmails($conn) {
    $sql = "SELECT COUNT(*) FROM early_access_emails";
    $result = $conn->query($sql);
    $row = $result->fetch_row();
    return $row[0];
}

// 3. ПОЛУЧЕНИЕ И РАСЧЕТ ДАННЫХ

// Статистика по сеткам
$tt_total = getTotalClicksByPrefix($conn, 'tt');
$tth_clicks = getHeaderClicks($conn, 'tth');
$tt_percentage = ($tt_total > 0) ? round(($tth_clicks / $tt_total) * 100) : 0;

$yt_total = getTotalClicksByPrefix($conn, 'yt');
$yth_clicks = getHeaderClicks($conn, 'yth');
$yt_percentage = ($yt_total > 0) ? round(($yth_clicks / $yt_total) * 100) : 0;

$in_total = getTotalClicksByPrefix($conn, 'ig');
$inh_clicks = getHeaderClicks($conn, 'igh');
$in_percentage = ($in_total > 0) ? round(($inh_clicks / $in_total) * 100) : 0;

// Статистика по платформам
$android_clicks = getPlatformClicks($conn, 'android');
$iphone_clicks = getPlatformClicks($conn, 'iphone');
$ipad_clicks = getPlatformClicks($conn, 'ipad');
$other_clicks = getPlatformClicks($conn, 'other'); // Добавляем clicks для платформы 'other'
$total_platform_clicks = $android_clicks + $iphone_clicks + $ipad_clicks + $other_clicks; // Включаем 'other' в общую сумму

$android_percentage = ($total_platform_clicks > 0) ? round(($android_clicks / $total_platform_clicks) * 100) : 0;
$ios_percentage = ($total_platform_clicks > 0) ? round((($iphone_clicks + $ipad_clicks) / $total_platform_clicks) * 100) : 0;
$other_percentage = ($total_platform_clicks > 0) ? round(($other_clicks / $total_platform_clicks) * 100) : 0; // Процент для 'other'

// Количество email
$total_emails = getTotalEmails($conn);

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click Statistics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #0056b3;
        }
        pre {
            background-color: #e9e9e9;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Click Statistics</h1>

        <h2>Clicks by Prefix:</h2>
        <p>tt = <?php echo $tt_total; ?> (<?php echo $tt_percentage; ?>% profile links)</p>
        <p>yt = <?php echo $yt_total; ?> (<?php echo $yt_percentage; ?>% profile links)</p>
        <p>ig = <?php echo $in_total; ?> (<?php echo $in_percentage; ?>% profile links)</p>

        <h2>Clicks by Platform:</h2>
        <p>Android = <?php echo $android_percentage; ?>%</p>
        <p>iOS = <?php echo $ios_percentage; ?>%</p>
        <p>Other = <?php echo $other_percentage; ?>%</p>

        <h2>Total Early Access Emails:</h2>
        <p><?php echo $total_emails; ?></p>

        <hr>
        <!--
        <h3>Raw Data (for debugging):</h3>
        <pre><?php print_r(get_defined_vars()); ?></pre>
        -->
    </div>
</body>
</html>
