<?php
include 'session.php';
include 'db.php';

// Función para sanitizar entradas
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Insertar Ocupación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $tipos_ocupacion = sanitizeInput($_POST['Tipos_Ocupación']);
    $id_empleado = sanitizeInput($_POST['ID_Empleado']);

    $sql = "INSERT INTO Ocupación (Tipos_Ocupación, ID_Empleado) 
            VALUES ('$tipos_ocupacion', '$id_empleado')";

    if ($conn->query($sql) === TRUE) {
        echo "Nueva ocupación registrada exitosamente<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Eliminar Ocupación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['ocupaciones'])) {
        $ocupaciones_a_eliminar = implode(",", $_POST['ocupaciones']);
        echo "Ocupaciones seleccionadas para eliminar: $ocupaciones_a_eliminar<br>";

        $sql_delete = "DELETE FROM Ocupación WHERE ID_Ocupación IN ($ocupaciones_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Ocupaciones eliminadas exitosamente.<br>";
        } else {
            echo "Error al eliminar ocupaciones: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ninguna ocupación para eliminar.<br>";
    }
}

// Editar Ocupación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_ocupacion = sanitizeInput($_POST['ID_Ocupación']);
    $tipos_ocupacion = sanitizeInput($_POST['Tipos_Ocupación']);
    $id_empleado = sanitizeInput($_POST['ID_Empleado']);

    $sql_update = "UPDATE Ocupación SET Tipos_Ocupación=?, ID_Empleado=? WHERE ID_Ocupación=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssi", $tipos_ocupacion, $id_empleado, $id_ocupacion);

    if ($stmt->execute()) {
        echo "Ocupación actualizada exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Ocupaciones
$sql = "SELECT * FROM Ocupación";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Ocupaciones</title>
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
    <h1>Gestionar Ocupaciones</h1>
    
    <h2>Insertar Nueva Ocupación</h2>
    <form method="post" action="">
        <label>Tipos de Ocupación:</label>
        <select name="Tipos_Ocupación" required>
            <option value="Limpieza">Limpieza</option>
            <option value="Seguridad">Seguridad</option>
            <option value="Servicio al cliente">Servicio al cliente</option>
        </select><br>
        <label>ID Empleado:</label><input type="number" name="ID_Empleado" required><br>
        <input type="submit" name="insertar" value="Insertar">
    </form>

    <h2>Ocupaciones Registradas</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Ocupación</th>
                <th>Tipos de Ocupación</th>
                <th>ID Empleado</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='ocupaciones[]' value='" . $row["ID_Ocupación"] . "' onclick='toggleEditButton(this)'></td>";
                    echo "<td>" . $row["ID_Ocupación"] . "</td>";
                    echo "<td>" . $row["Tipos_Ocupación"] . "</td>";
                    echo "<td>" . $row["ID_Empleado"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No hay ocupaciones registradas</td></tr>";
            }
            ?>
        </table>
        <input type="submit" name="eliminar" value="Eliminar Ocupaciones">
        <input type="button" id="editButton" value="Editar Ocupación" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <h2>Editar Ocupación</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Ocupación" id="editID" required>
        <label>Tipos de Ocupación:</label>
        <select name="Tipos_Ocupación" id="editTipos_Ocupación" required>
            <option value="Limpieza">Limpieza</option>
            <option value="Seguridad">Seguridad</option>
            <option value="Servicio al cliente">Servicio al cliente</option>
        </select><br>
        <label>ID Empleado:</label><input type="number" name="ID_Empleado" id="editID_Empleado" required><br>
        <input type="submit" name="editar" value="Actualizar" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="ocupaciones[]"]:checked');
        }

        function editSelected() {
            const selectedCheckbox = document.querySelector('input[name="ocupaciones[]"]:checked');
            if (selectedCheckbox) {
                const row = selectedCheckbox.closest('tr');
                const cells = row.getElementsByTagName('td');
                
                document.getElementById('editID').value = selectedCheckbox.value;
                document.getElementById('editTipos_Ocupación').value = cells[2].innerText; // Tipos de Ocupación
                document.getElementById('editID_Empleado').value = cells[3].innerText; // ID Empleado

                document.getElementById('editForm').classList.remove('hidden');
            }
        }
    </script>

</body>
</html>

<?php
$conn->close();
?>
