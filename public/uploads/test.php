<?php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=ct275_lab3  ", "postgres", "123456789");
    echo "Kết nối thành công!";
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
