<?php
include 'session.php';
include 'db.php';

// Función para sanitizar entradas
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Insertar Mantenimiento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $fecha = sanitizeInput($_POST['Fecha']);
    $descripcion = sanitizeInput($_POST['Descripción']);
    $estado = sanitizeInput($_POST['Estado']);
    $id_empleado = sanitizeInput($_POST['ID_Empleado']);
    $id_habitacion = sanitizeInput($_POST['ID_Habitación']);

    $sql = "INSERT INTO Mantenimiento (Fecha, Descripción, Estado, ID_Empleado, ID_Habitación) 
            VALUES ('$fecha', '$descripcion', '$estado', '$id_empleado', '$id_habitacion')";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo mantenimiento registrado exitosamente<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Eliminar Mantenimientos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['mantenimientos'])) {
        $mantenimientos_a_eliminar = implode(",", $_POST['mantenimientos']);
        echo "Mantenimientos seleccionados para eliminar: $mantenimientos_a_eliminar<br>";

        $sql_delete = "DELETE FROM Mantenimiento WHERE ID_Mantenimiento IN ($mantenimientos_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Mantenimientos eliminados exitosamente.<br>";
        } else {
            echo "Error al eliminar mantenimientos: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ningún mantenimiento para eliminar.<br>";
    }
}

// Editar Mantenimiento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_mantenimiento = sanitizeInput($_POST['ID_Mantenimiento']);
    $fecha = sanitizeInput($_POST['Fecha']);
    $descripcion = sanitizeInput($_POST['Descripción']);
    $estado = sanitizeInput($_POST['Estado']);
    $id_empleado = sanitizeInput($_POST['ID_Empleado']);
    $id_habitacion = sanitizeInput($_POST['ID_Habitación']);

    $sql_update = "UPDATE Mantenimiento SET Fecha=?, Descripción=?, Estado=?, ID_Empleado=?, ID_Habitación=? WHERE ID_Mantenimiento=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssiis", $fecha, $descripcion, $estado, $id_empleado, $id_habitacion, $id_mantenimiento);

    if ($stmt->execute()) {
        echo "Mantenimiento actualizado exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Mantenimientos
$sql = "SELECT * FROM Mantenimiento";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Mantenimiento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .hidden {
            display: none;
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
    </style>
</head>
<body>
    <h1>Gestionar Mantenimiento</h1>
    
    <h2>Insertar Nuevo Mantenimiento</h2>
    <form method="post" action="">
        <label>Fecha:</label><input type="date" name="Fecha" required><br>
        <label>Descripción:</label><input type="text" name="Descripción" required><br>
        <label>Estado:</label>
        <select name="Estado" required>
            <option value="Pendiente">Pendiente</option>
            <option value="Completado">Completado</option>
        </select><br>
        <label>ID Empleado:</label><input type="number" name="ID_Empleado" required><br>
        <label>ID Habitación:</label><input type="number" name="ID_Habitación" required><br>
        <input type="submit" name="insertar" value="Insertar">
    </form>

    <h2>Mantenimientos Registrados</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Mantenimiento</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>ID Empleado</th>
                <th>ID Habitación</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='mantenimientos[]' value='" . $row["ID_Mantenimiento"] . "' onclick='toggleEditButton(this)'></td>";
                    echo "<td>" . $row["ID_Mantenimiento"] . "</td>";
                    echo "<td>" . $row["Fecha"] . "</td>";
                    echo "<td>" . $row["Descripción"] . "</td>";
                    echo "<td>" . $row["Estado"] . "</td>";
                    echo "<td>" . $row["ID_Empleado"] . "</td>";
                    echo "<td>" . $row["ID_Habitación"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay mantenimientos registrados</td></tr>";
            }
            ?>
        </table>
        <input type="submit" name="eliminar" value="Eliminar Mantenimientos">
        <input type="button" id="editButton" value="Editar Mantenimiento" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <h2>Editar Mantenimiento</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Mantenimiento" id="editID" required>
        <label>Fecha:</label><input type="date" name="Fecha" id="editFecha" required><br>
        <label>Descripción:</label><input type="text" name="Descripción" id="editDescripcion" required><br>
        <label>Estado:</label>
        <select name="Estado" id="editEstado" required>
            <option value="Pendiente">Pendiente</option>
            <option value="Completado">Completado</option>
        </select><br>
        <label>ID Empleado:</label><input type="number" name="ID_Empleado" id="editID_Empleado" required><br>
        <label>ID Habitación:</label><input type="number" name="ID_Habitación" id="editID_Habitación" required><br>
        <input type="submit" name="editar" value="Actualizar" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="mantenimientos[]"]:checked');
        }

        function editSelected() {
            const selectedCheckbox = document.querySelector('input[name="mantenimientos[]"]:checked');
            if (selectedCheckbox) {
                const row = selectedCheckbox.closest('tr');
                const cells = row.getElementsByTagName('td');
                
                document.getElementById('editID').value = selectedCheckbox.value;
                document.getElementById('editFecha').value = cells[2].innerText; // Fecha
                document.getElementById('editDescripcion').value = cells[3].innerText; // Descripción
                document.getElementById('editEstado').value = cells[4].innerText; // Estado
                document.getElementById('editID_Empleado').value = cells[5].innerText; // ID Empleado
                document.getElementById('editID_Habitación').value = cells[6].innerText; // ID Habitación

                document.getElementById('editForm').classList.remove('hidden');
            }
        }
    </script>

</body>
</html>

<?php
$conn->close();
?>
