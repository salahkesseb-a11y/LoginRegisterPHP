<?php
$host = 'localhost';
$db   = 'student_db';
$user = 'root';
$pass = '';


$dsn = "mysql:host=$host;dbname=$db";
$options = [
     PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}
?>
