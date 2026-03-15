<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307', 'root', '');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS sencrime CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo 'Database sencrime created successfully!';
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
