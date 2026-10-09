<?php
$host = "mysql.railway.internal";
$username = "root";
$password = "Mohamed90##";
$database = "build3";
$port = "3306";

$conn = mysqli_connect($host, $username, $password, $database, $port);

// ضبط ترميز اللغة العربية عشان الحروف تظهر صحيحة
mysqli_set_charset($conn, "utf8");

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}
?>