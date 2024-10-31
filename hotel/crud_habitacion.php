<?php
include 'session.php';
include 'db.php';

// Función para sanitizar entradas
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Insertar Habitación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $tipo = sanitizeInput($_POST['Tipo']);
    $precio = sanitizeInput($_POST['Precio']);
    $estado = sanitizeInput($_POST['Estado']);
    $habitacion = isset($_POST['Habitacion']) ? sanitizeInput($_POST['Habitacion']) : null;
    $descripcion = sanitizeInput($_POST['Descripcion']);

    // Validar la habitación seleccionada para determinar el piso
    if ($habitacion >= 1 && $habitacion <= 10) {
        $habitacionPiso = "Abajo";
    } elseif ($habitacion >= 11 && $habitacion <= 20) {
        $habitacionPiso = "Arriba";
    } else {
        echo "Error: Selección de habitación no válida.<br>";
        exit;
    }

    $sql = "INSERT INTO Habitación (Tipo, Precio, Estado, HabitacionPiso, Descripción, Numero_Habitacion) 
            VALUES ('$tipo', '$precio', '$estado', '$habitacionPiso', '$descripcion', '$habitacion')";

    if ($conn->query($sql) === TRUE) {
        echo "Nueva habitación registrada exitosamente<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Eliminar Habitaciones
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['habitaciones'])) {
        $habitaciones_a_eliminar = implode(",", $_POST['habitaciones']);
        echo "Habitaciones seleccionadas para eliminar: $habitaciones_a_eliminar<br>";

        $sql_delete = "DELETE FROM Habitación WHERE ID_Habitación IN ($habitaciones_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Habitaciones eliminadas exitosamente.<br>";
        } else {
            echo "Error al eliminar habitaciones: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ninguna habitación para eliminar.<br>";
    }
}

// Editar Habitación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_habitacion = sanitizeInput($_POST['ID_Habitacion']);
    $tipo = sanitizeInput($_POST['Tipo']);
    $precio = sanitizeInput($_POST['Precio']);
    $estado = sanitizeInput($_POST['Estado']);
    $descripcion = sanitizeInput($_POST['Descripcion']);

    // Obtener el piso según el número de habitación
    if ($id_habitacion >= 1 && $id_habitacion <= 10) {
        $habitacionPiso = "Abajo";
    } else {
        $habitacionPiso = "Arriba";
    }

    $sql_update = "UPDATE Habitación SET Tipo=?, Precio=?, Estado=?, HabitacionPiso=?, Descripción=? WHERE ID_Habitación=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sdsssi", $tipo, $precio, $estado, $habitacionPiso, $descripcion, $id_habitacion);

    if ($stmt->execute()) {
        echo "Habitación actualizada exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Habitaciones
$sql = "SELECT * FROM Habitación";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Habitaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .boton-actualizar {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .boton-actualizar:hover {
            background-color: darkblue;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Gestionar Habitaciones</h1>
    
    <h2>Insertar Nueva Habitación</h2>
    <form method="post" action="">
        <label>Tipo:</label>
        <select name="Tipo" required>
            <option value="Sencilla">Sencilla</option>
            <option value="Doble">Doble</option>
            <option value="Suite">Suite</option>
        </select><br>
        <label>Precio:</label><input type="number" step="0.01" name="Precio" required><br>
        <label>Estado:</label>
        <select name="Estado" required>
            <option value="Disponible">Disponible</option>
            <option value="Ocupada">Ocupada</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select><br>
        <label>Habitación:</label>
        <select name="Habitacion" required>
            <?php
            // Generar las opciones de habitación
            for ($i = 1; $i <= 20; $i++) {
                if ($i <= 10) {
                    echo "<option value='$i'>Habitación $i - Piso de Abajo</option>";
                } else {
                    echo "<option value='$i'>Habitación $i - Piso de Arriba</option>";
                }
            }
            ?>
        </select><br>
        <label>Descripción:</label><input type="text" name="Descripcion" required><br>
        <input type="submit" name="insertar" value="Insertar Habitación">
    </form>

    <h2>Habitaciones Registradas</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Habitación</th>
                <th>Número de Habitación</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Piso</th>
                <th>Descripción</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='habitaciones[]' value='" . $row["ID_Habitación"] . "' onclick='toggleEditButton(this)'></td>";
                    echo "<td>" . $row["ID_Habitación"] . "</td>";
                    echo "<td>" . $row["Numero_Habitacion"] . "</td>";
                    echo "<td>" . $row["Tipo"] . "</td>";
                    echo "<td>" . $row["Precio"] . "</td>";
                    echo "<td>" . $row["Estado"] . "</td>";
                    echo "<td>" . $row["HabitacionPiso"] . "</td>";
                    echo "<td>" . $row["Descripción"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No hay habitaciones registradas</td></tr>";
            }
            ?>
        </table>
        <input type="submit" name="eliminar" value="Eliminar Habitaciones">
        <input type="button" id="editButton" value="Editar Habitación" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <h2>Editar Habitación</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Habitacion" id="editID" required>
        <label>Tipo:</label>
        <select name="Tipo" id="editTipo" required>
            <option value="Sencilla">Sencilla</option>
            <option value="Doble">Doble</option>
            <option value="Suite">Suite</option>
        </select><br>
        <label>Precio:</label><input type="number" step="0.01" name="Precio" id="editPrecio" required><br>
        <label>Estado:</label>
        <select name="Estado" id="editEstado" required>
            <option value="Disponible">Disponible</option>
            <option value="Ocupada">Ocupada</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select><br>
        <label>Descripción:</label><input type="text" name="Descripcion" id="editDescripcion" required><br>
        <input type="submit" name="editar" value="Actualizar Habitación" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="habitaciones[]"]:checked');
        }

        function editSelected() {
            const selectedCheckbox = document.querySelector('input[name="habitaciones[]"]:checked');
            if (selectedCheckbox) {
                const row = selectedCheckbox.closest('tr');
                const cells = row.getElementsByTagName('td');
                
                document.getElementById('editID').value = selectedCheckbox.value;
                document.getElementById('editTipo').value = cells[3].innerText; // Tipo
                document.getElementById('editPrecio').value = cells[4].innerText; // Precio
                document.getElementById('editEstado').value = cells[5].innerText; // Estado
                document.getElementById('editDescripcion').value = cells[7].innerText; // Descripción

                document.getElementById('editForm').classList.remove('hidden');
            }
        }
    </script>
    
</body>
</html>

<?php
$conn->close();
?>
