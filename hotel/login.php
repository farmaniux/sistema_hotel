<?php
session_start();

// Verifica si el usuario ya ha iniciado sesión
if (isset($_SESSION['user'])) {
    header("Location: index11.php");
    exit();
}

// Mensaje de error si el inicio de sesión falla
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el nombre de usuario, contraseña y rol desde el formulario
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Conectar a la base de datos
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "hotel";

    // Crear conexión
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Consulta para verificar el usuario y el rol
    $sql = "SELECT * FROM Usuario WHERE Nombre_Usuario = ? AND Rol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró un usuario
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verificar la contraseña directamente
        if ($password === $row['Contraseña']) {
            $_SESSION['user'] = $row['Nombre_Usuario']; // Guarda el nombre de usuario en la sesión
            $_SESSION['role'] = $row['Rol']; // Guarda el rol en la sesión
            header("Location: index11.php"); // Redirige al índice
            exit();
        } else {
            $error = 'Contraseña incorrecta.';
        }
    } else {
        $error = 'Usuario o rol incorrectos.';
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #0072ff, #00c6ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
            text-align: center;
        }
        .container {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.7);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: calc(100% - 20px);
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 1.1em;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        input[type="submit"] {
            background: #ff4081;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            width: 50%;
            transition: background 0.3s;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background: #ff2c60;
        }
        .error {
            color: #ffdddd;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Iniciar sesión</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST" autocomplete="off">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required autocomplete="off">
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required autocomplete="off">

            <label for="role">Selecciona el rol:</label>
            <select id="role" name="role" required>
                <option value="admin">Administrador</option>
                <option value="recepcionista">Recepcionista</option>
                <option value="mantenimiento">Mantenimiento</option>
            </select>
            
            <input type="submit" value="Iniciar sesión">
        </form>
    </div>
</body>
</html>
