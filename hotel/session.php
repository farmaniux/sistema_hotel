<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Solo inicia la sesión si no hay ninguna activa
}

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

