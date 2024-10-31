<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel";

// Crear conexión
$conn = new mysqli($servername, $username, $password);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos '$dbname' creada exitosamente<br>";
} else {
    echo "Error al crear la base de datos: " . $conn->error . "<br>";
}

// Seleccionar la base de datos
$conn->select_db($dbname);

// Recuperar los datos del formulario POST, incluyendo método de pago y número de transacción
$metodo_pago = $_POST['Metodo_Pago'] ?? null;
$numero_transaccion = $_POST['Numero_Transaccion'] ?? null;

// Crear las tablas si no existen
$tables = [
    "Cliente" => "CREATE TABLE IF NOT EXISTS Cliente (
        ID_Cliente INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100),
        Apellido VARCHAR(100),
        Email VARCHAR(100),
        Teléfono VARCHAR(20),
        Dirección VARCHAR(255),
        Fecha_Nacimiento DATE,
        Género VARCHAR(10),
        Nacionalidad VARCHAR(50),
        Tipo_Cliente ENUM('Frecuente', 'Nuevo', 'VIP'),
        Fecha_Registro DATE
    )",

    "Habitación" => "CREATE TABLE IF NOT EXISTS Habitación (
        ID_Habitación INT AUTO_INCREMENT PRIMARY KEY,
        Tipo ENUM('Sencilla', 'Doble', 'Suite'),
        Precio DECIMAL(10, 2),
        Estado ENUM('Disponible', 'Ocupada', 'Mantenimiento'),
        Descripción VARCHAR(255),
        HabitacionPiso VARCHAR(30), -- Nueva columna que incluye el piso
        Numero_Habitacion INT -- Nueva columna para el número de habitación
    )",

    "Reserva" => "CREATE TABLE IF NOT EXISTS Reserva (
        ID_Reserva INT AUTO_INCREMENT PRIMARY KEY,
        Fecha_Inicio DATE,
        Fecha_Fin DATE,
        Estado ENUM('Confirmada', 'Cancelada', 'Pendiente'),
        Cantidad_Personas INT,
        Metodo_Pago ENUM('Tarjeta', 'Efectivo', 'Transferencia'), 
        Numero_Transaccion VARCHAR(50),
        ID_Cliente INT,
        ID_Habitación INT,
        FOREIGN KEY (ID_Cliente) REFERENCES Cliente(ID_Cliente),
        FOREIGN KEY (ID_Habitación) REFERENCES Habitación(ID_Habitación)
    )",

    "Servicio" => "CREATE TABLE IF NOT EXISTS Servicio (
        ID_Servicio INT AUTO_INCREMENT PRIMARY KEY,
        Nombre_Servicio VARCHAR(100),  -- Cambié ENUM a VARCHAR para permitir cualquier valor
        Descripción VARCHAR(255),
        Costo DECIMAL(10, 2),
        Horario_Disponible VARCHAR(50),
        ID_Reserva INT,
        FOREIGN KEY (ID_Reserva) REFERENCES Reserva(ID_Reserva)
    )",

    "Empleado" => "CREATE TABLE IF NOT EXISTS Empleado (
        ID_Empleado INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100),
        Apellido VARCHAR(100),
        Cargo ENUM('Recepcionista', 'Administrador', 'Mantenimiento'),
        Fecha_Contratación DATE,
        Email VARCHAR(100),
        Teléfono VARCHAR(20),
        Departamento ENUM('Recepción', 'Administración', 'Mantenimiento')
    )",

    "Mantenimiento" => "CREATE TABLE IF NOT EXISTS Mantenimiento (
        ID_Mantenimiento INT AUTO_INCREMENT PRIMARY KEY,
        Fecha DATE,
        Descripción VARCHAR(255),
        Estado ENUM('Pendiente', 'Completado'),
        ID_Empleado INT,
        ID_Habitación INT,
        FOREIGN KEY (ID_Empleado) REFERENCES Empleado(ID_Empleado),
        FOREIGN KEY (ID_Habitación) REFERENCES Habitación(ID_Habitación)
    )",

    "Factura" => "CREATE TABLE IF NOT EXISTS Factura (
        ID_Factura INT AUTO_INCREMENT PRIMARY KEY,
        Fecha_Emisión DATE,
        Monto_Total DECIMAL(10, 2),
        Descripción_Detallada TEXT,
        ID_Cliente INT,
        FOREIGN KEY (ID_Cliente) REFERENCES Cliente(ID_Cliente)
    )",

    "Tipo_Usuario" => "CREATE TABLE IF NOT EXISTS Tipo_Usuario (
        ID_Tipo_Usuario INT AUTO_INCREMENT PRIMARY KEY,
        Descripción VARCHAR(50)
    )",

    "Ocupación" => "CREATE TABLE IF NOT EXISTS Ocupación (
        ID_Ocupación INT AUTO_INCREMENT PRIMARY KEY,
        Tipos_Ocupación ENUM('Limpieza', 'Seguridad', 'Servicio al cliente'),
        ID_Empleado INT,
        FOREIGN KEY (ID_Empleado) REFERENCES Empleado(ID_Empleado)
    )",

    "Usuario" => "CREATE TABLE IF NOT EXISTS Usuario (
        ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
        Nombre_Usuario VARCHAR(50) NOT NULL UNIQUE,
        Contraseña VARCHAR(255) NOT NULL,
        Rol ENUM('admin', 'recepcionista', 'mantenimiento') NOT NULL
    )"
];

// Ejecutar las consultas para crear las tablas
foreach ($tables as $table => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Tabla '$table' creada exitosamente<br>";
    } else {
        echo "Error al crear la tabla '$table': " . $conn->error . "<br>";
    }
}

// Crear los disparadores (triggers)
$triggers = [
    "ReservaEstadoHabitacion" => "CREATE TRIGGER ReservaEstadoHabitacion 
        AFTER INSERT ON Reserva 
        FOR EACH ROW 
        UPDATE Habitación 
        SET Estado = 'Ocupada' 
        WHERE ID_Habitación = NEW.ID_Habitación;",
    
    "CancelacionReservaHabitacion" => "CREATE TRIGGER CancelacionReservaHabitacion 
        AFTER UPDATE ON Reserva 
        FOR EACH ROW 
        IF NEW.Estado = 'Cancelada' THEN
            UPDATE Habitación 
            SET Estado = 'Disponible' 
            WHERE ID_Habitación = NEW.ID_Habitación;
        END IF;",

    "FechaContratacionEmpleado" => "CREATE TRIGGER FechaContratacionEmpleado 
        BEFORE INSERT ON Empleado 
        FOR EACH ROW 
        SET NEW.Fecha_Contratación = NOW();",
    
    "FechaMantenimientoCompletado" => "CREATE TRIGGER FechaMantenimientoCompletado 
        AFTER UPDATE ON Mantenimiento 
        FOR EACH ROW 
        IF NEW.Estado = 'Completado' THEN
            UPDATE Mantenimiento 
            SET Fecha = NOW() 
            WHERE ID_Mantenimiento = NEW.ID_Mantenimiento;
        END IF;"
];

// Ejecutar las consultas para crear los disparadores
foreach ($triggers as $trigger => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Trigger '$trigger' creado exitosamente<br>";
    } else {
        echo "Error al crear el trigger '$trigger': " . $conn->error . "<br>";
    }
}

// Crear los usuarios con contraseñas en texto plano
$sql = "INSERT INTO Usuario (Nombre_Usuario, Contraseña, Rol) VALUES 
    ('admin', 'admin123', 'admin'), 
    ('recepcionista', 'recep123', 'recepcionista'), 
    ('mantenimiento', 'mant123', 'mantenimiento') 
    ON DUPLICATE KEY UPDATE Nombre_Usuario = Nombre_Usuario"; // Evitar duplicados

if ($conn->query($sql) === TRUE) {
    echo "Usuarios insertados con éxito.<br>";
} else {
    echo "Error al insertar usuarios: " . $conn->error . "<br>";
}

// Crear las rutinas (stored procedures)
$routines = [
    "TotalIngresosPorFecha" => "CREATE PROCEDURE TotalIngresosPorFecha(IN fecha_inicio DATE, IN fecha_fin DATE)
        BEGIN
            SELECT SUM(Monto_Total) AS Total_Ingresos 
            FROM Factura 
            WHERE Fecha_Emisión BETWEEN fecha_inicio AND fecha_fin;
        END;",

    "ListarClientesFrecuentes" => "CREATE PROCEDURE ListarClientesFrecuentes()
        BEGIN
            SELECT Nombre, Apellido, Tipo_Cliente 
            FROM Cliente 
            WHERE Tipo_Cliente = 'Frecuente';
        END;",

    "AsignarHabitacionDisponible" => "CREATE PROCEDURE AsignarHabitacionDisponible(IN tipo_habitacion ENUM('Sencilla', 'Doble', 'Suite'))
        BEGIN
            SELECT ID_Habitación 
            FROM Habitación 
            WHERE Tipo = tipo_habitacion AND Estado = 'Disponible' 
            LIMIT 1;
        END;",

    "ContarReservasPorCliente" => "CREATE PROCEDURE ContarReservasPorCliente(IN id_cliente INT)
        BEGIN
            SELECT COUNT(*) AS Total_Reservas 
            FROM Reserva 
            WHERE ID_Cliente = id_cliente;
        END;"
];

// Ejecutar las consultas para crear las rutinas
foreach ($routines as $routine => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Procedimiento '$routine' creado exitosamente<br>";
    } else {
        echo "Error al crear el procedimiento '$routine': " . $conn->error . "<br>";
    }
}

// Cerrar la conexión
$conn->close();
?>
