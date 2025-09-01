<?php
$host = "sql306.infinityfree.com"; //"192.168.8.116"; 
$user = "if0_39842559"; //"watch_backend";
$password = "puNu6Dmnt44z"; //"WatchBackend2004";
$dbname = "if0_39842559_watch_sql"; //"watch-backend";

try {
    $connect = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Connected successfully to DB!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
