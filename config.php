<?php
// config.php - Файл конфигурации базы данных

$servername = "localhost";
$username = "cy74408_stat";
$password = "NWWb94Xq";
$dbname = "cy74408_stat";
$dbstatport = '3306';

/*
SQL Queries for Database Tables:

1. Table `clicks` (for tracker.php)
CREATE TABLE clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link_code VARCHAR(255) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

2. Table `early_access_emails` (for early_access.php)
CREATE TABLE early_access_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
*/

?>
