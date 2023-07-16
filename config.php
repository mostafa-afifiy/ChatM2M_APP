<?php
$dsn = "mysql:host=localhost;dbname=faceyou";
$user = "root";
$pass = "";
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);
try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
    echo "failed to connect to database " . $e->getMessage();
}
?>