<?php
$servername = "localhost";
$username = "root";  // o el usuario que uses
$password = "";  // tu contraseña
$dbname = "hotel";  // cambia esto por el nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>