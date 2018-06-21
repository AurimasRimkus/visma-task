<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "visma";
$charset = 'utf8mb4';

$dsn = "mysql:host=$server;dbname=$database;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $username, $password, $opt);
