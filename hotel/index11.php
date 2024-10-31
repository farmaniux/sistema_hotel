<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Verifica si el usuario es administrador
$is_recepcionista = isset($_SESSION['role']) && $_SESSION['role'] === 'recepcionista'; // Verifica si el usuario es recepcionista
$is_mantenimiento = isset($_SESSION['role']) && $_SESSION['role'] === 'mantenimiento'; // Verifica si el usuario es de mantenimiento

include 'db.php';

// Lógica para cerrar sesión
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Gestión Hotelera</title>
    <style>
        /* Estilo general */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            background-image: url('IMAGENES/hola3.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #2c3e50; 
            margin: 0;
            padding: 0;
            display: flex; /* Flexbox para la distribución */
        }

        /* Menú lateral */
        #menu {
            width: 220px; /* Ancho del menú */
            background-color: rgba(128, 0, 128, 0.9); /* Morado */
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            padding: 20px;
            position: fixed; /* Fijo a la izquierda */
            height: 100%; /* Altura completa */
            overflow-y: auto; /* Scroll si es necesario */
        }

        #menu h2 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }

        #menu a {
            display: block;
            text-decoration: none;
            padding: 15px;
            margin: 10px 0;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: bold;
            text-align: center; /* Centrar texto */
        }

        #menu a:hover {
            background-color: #9b59b6; /* Morado más claro al pasar el mouse */
            transform: translateY(-3px);
        }

        /* Contenedor principal */
        #container {
            margin-left: 240px; /* Espacio para el menú */
            width: calc(100% - 240px); /* Ajuste de ancho */
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            min-height: 100vh; /* Mínima altura del contenedor */
        }

        /* Estilo del encabezado */
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin: 20px 0;
            color: #8e44ad; /* Color morado oscuro */
        }

        /* Botón de cerrar sesión */
        #logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #e74c3c; /* Color rojo */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        #logout-button:hover {
            background-color: #c0392b; /* Rojo más oscuro al pasar el mouse */
            transform: scale(1.05); /* Efecto de aumentar tamaño al pasar el mouse */
        }

        /* Estilo del contenido */
        #content {
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9; /* Fondo más claro para el contenido */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Estilo de los formularios */
        form {
            margin-top: 20px;
        }

        label {
            display: inline-block;
            width: 150px;
            font-size: 1.1em;
            color: #8e44ad; /* Color de la etiqueta morado */
            margin-bottom: 10px;
        }

        input[type="text"], input[type="email"], input[type="date"], select {
            width: calc(100% - 160px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #8e44ad; /* Borde morado */
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="date"]:focus, select:focus {
            border-color: #9b59b6; /* Color de borde al enfocar */
            outline: none; /* Quitar contorno por defecto */
        }

        /* Estilo de las tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #8e44ad;
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #8e44ad;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Botones de acción */
        input[type="submit"][value^="Insertar"],
        button[type="submit"][value^="Insertar"] {
            background-color: #2ecc71; /* Verde */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        input[type="submit"][value^="Insertar"]:hover,
        button[type="submit"][value^="Insertar"]:hover {
            background-color: #27ae60; /* Verde más oscuro al pasar el mouse */
            transform: scale(1.05); /* Efecto de aumentar tamaño al pasar el mouse */
        }

        /* Botones de Eliminar */
        input[type="submit"][value^="Eliminar"],
        button[type="submit"][value^="Eliminar"] {
            background-color: #e74c3c; /* Rojo */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        input[type="submit"][value^="Eliminar"]:hover,
        button[type="submit"][value^="Eliminar"]:hover {
            background-color: #c0392b; /* Rojo más oscuro al pasar el mouse */
            transform: scale(1.05); /* Efecto de aumentar tamaño al pasar el mouse */
        }
    </style>
</head>
<body>
    <div id="menu">
        <h2>Menú</h2>
        <a href="?page=clientes">Clientes</a>
        <a href="?page=habitaciones">Habitaciones</a>
        <a href="?page=reservas">Reservas</a>
        <a href="?page=servicios">Servicios</a>
        <a href="?page=facturas">Facturas</a>
        <?php if ($is_admin): ?>
            <a href="?page=empleados">Empleados</a>
            <a href="?page=mantenimiento">Mantenimiento</a>
            <a href="?page=tipos_usuarios">Tipos de Usuario</a>
            <a href="?page=ocupaciones">Ocupaciones</a>
        <?php endif; ?>
        <?php if ($is_recepcionista): ?>
            <a href="?page=reservas">Reservas</a>
            <a href="?page=clientes">Clientes</a>
        <?php endif; ?>
        <?php if ($is_mantenimiento): ?>
            <a href="?page=mantenimiento">Mantenimiento</a>
        <?php endif; ?>
    </div>
    <div id="container">
        <h1>
            <!-- Aquí se debe colocar la imagen del logo circular -->
            <img src="IMAGENES/logo1.png" style="width: 120px; border-radius: 50%;" alt="Logo">
            Sistema de Gestión Hotelera
        </h1>
        <form action="?action=logout" method="post">
            <input id="logout-button" type="submit" value="Cerrar Sesión">
        </form>

        <div id="content">
            <?php
            // Mostrar contenido basado en la página seleccionada
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';

            switch ($page) {
                case 'clientes':
                    include 'crud_cliente.php';
                    break;
                case 'habitaciones':
                    include 'crud_habitacion.php';
                    break;
                case 'reservas':
                    include 'crud_reserva.php';
                    break;
                case 'servicios':
                    include 'crud_servicio.php';
                    break;
                case 'facturas':
                    include 'crud_factura.php';
                    break;
                case 'empleados':
                    if ($is_admin) {
                        include 'crud_empleado.php';
                    } else {
                        echo "<p>No tienes permiso para acceder a esta sección.</p>";
                    }
                    break;
                case 'mantenimiento':
                    if ($is_admin || $is_mantenimiento) {
                        include 'crud_mantenimiento.php';
                    } else {
                        echo "<p>No tienes permiso para acceder a esta sección.</p>";
                    }
                    break;
                case 'tipos_usuarios':
                    if ($is_admin) {
                        include 'crud_tipo_usuario.php';
                    } else {
                        echo "<p>No tienes permiso para acceder a esta sección.</p>";
                    }
                    break;
                case 'ocupaciones':
                    if ($is_admin) {
                        include 'crud_ocupacion.php';
                    } else {
                        echo "<p>No tienes permiso para acceder a esta sección.</p>";
                    }
                    break;
                default:
                    echo "<h2>Bienvenido al Sistema de Gestión Hotelera</h2>";
                    echo "<p>Selecciona una opción del menú.</p>";
                    break;
            }
            ?>
        </div>
    </div>
</body>
</html>
