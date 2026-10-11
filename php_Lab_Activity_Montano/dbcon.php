PHP

<?php
$conn = new mysqli("localhost", "root", "", "php_db");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
echo "Database connected successfully!";
?>