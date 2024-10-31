<?php
include 'session.php';
include 'db.php';

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $nombre = sanitizeInput($_POST['Nombre']);
    $apellido = sanitizeInput($_POST['Apellido']);
    $cargo = sanitizeInput($_POST['Cargo']);
    $fecha_contratacion = sanitizeInput($_POST['Fecha_Contratación']);
    $email = sanitizeInput($_POST['Email']);
    $telefono = sanitizeInput($_POST['Teléfono']);
    $departamento = sanitizeInput($_POST['Departamento']);

    $stmt = $conn->prepare("INSERT INTO Empleado (Nombre, Apellido, Cargo, Fecha_Contratación, Email, Teléfono, Departamento) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nombre, $apellido, $cargo, $fecha_contratacion, $email, $telefono, $departamento);

    if ($stmt->execute()) {
        echo "Nuevo empleado registrado exitosamente<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Eliminar Empleado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['empleados'])) {
        $empleados_a_eliminar = implode(",", $_POST['empleados']);
        echo "Empleados seleccionados para eliminar: $empleados_a_eliminar<br>";

        $sql_delete = "DELETE FROM Empleado WHERE ID_Empleado IN ($empleados_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Empleados eliminados exitosamente.<br>";
        } else {
            echo "Error al eliminar empleados: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ningún empleado para eliminar.<br>";
    }
}

// Editar Empleado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_empleado = sanitizeInput($_POST['ID_Empleado']);
    $nombre = sanitizeInput($_POST['Nombre']);
    $apellido = sanitizeInput($_POST['Apellido']);
    $cargo = sanitizeInput($_POST['Cargo']);
    $fecha_contratacion = sanitizeInput($_POST['Fecha_Contratación']);
    $email = sanitizeInput($_POST['Email']);
    $telefono = sanitizeInput($_POST['Teléfono']);
    $departamento = sanitizeInput($_POST['Departamento']);

    $stmt = $conn->prepare("UPDATE Empleado SET Nombre=?, Apellido=?, Cargo=?, Fecha_Contratación=?, Email=?, Teléfono=?, Departamento=? WHERE ID_Empleado=?");
    $stmt->bind_param("sssssssi", $nombre, $apellido, $cargo, $fecha_contratacion, $email, $telefono, $departamento, $id_empleado);

    if ($stmt->execute()) {
        echo "Empleado actualizado exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Empleados
$sql = "SELECT * FROM Empleado";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Empleados</title>
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
            background-color: darkblue; /* Color al pasar el mouse */
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Gestionar Empleados</h1>

    <!-- Formulario de Inserción -->
    <h2>Insertar Nuevo Empleado</h2>
    <form method="post" action="">
        <label>Nombre:</label><input type="text" name="Nombre" required><br>
        <label>Apellido:</label><input type="text" name="Apellido" required><br>
        <label>Cargo:</label>
        <select name="Cargo" required>
            <option value="Recepcionista">Recepcionista</option>
            <option value="Administrador">Administrador</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select><br>
        <label>Fecha de Contratación:</label><input type="date" name="Fecha_Contratación" required><br>
        <label>Email:</label><input type="email" name="Email" required><br>
        <label>Teléfono:</label><input type="text" name="Teléfono" required><br>
        <label>Departamento:</label>
        <select name="Departamento" required>
            <option value="Recepción">Recepción</option>
            <option value="Administración">Administración</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select><br>
        <input type="submit" name="insertar" value="Insertar Empleado" class="boton-actualizar">
    </form>

    <!-- Formulario para Eliminar Empleados -->
    <form method="post" action="">
        <h2>Empleados Registrados</h2>
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Empleado</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Cargo</th>
                <th>Fecha de Contratación</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Departamento</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='empleados[]' value='" . $row["ID_Empleado"] . "' onclick='toggleEditButton(this)'></td>"; // Casilla de selección
                    echo "<td>" . $row["ID_Empleado"] . "</td>";
                    echo "<td>" . $row["Nombre"] . "</td>";
                    echo "<td>" . $row["Apellido"] . "</td>";
                    echo "<td>" . $row["Cargo"] . "</td>";
                    echo "<td>" . $row["Fecha_Contratación"] . "</td>";
                    echo "<td>" . $row["Email"] . "</td>";
                    echo "<td>" . $row["Teléfono"] . "</td>";
                    echo "<td>" . $row["Departamento"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No hay empleados registrados</td></tr>";
            }
            ?>
        </table>
        <br>
        <input type="submit" name="eliminar" value="Eliminar Empleado" class="boton-actualizar">
        <input type="button" id="editButton" value="Editar Empleado" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <!-- Formulario para Editar Empleado -->
    <h2>Editar Empleado</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Empleado" id="editID" required>
        <label>Nombre:</label><input type="text" name="Nombre" id="editNombre" required><br>
        <label>Apellido:</label><input type="text" name="Apellido" id="editApellido" required><br>
        <label>Cargo:</label>
        <select name="Cargo" id="editCargo" required>
            <option value="Recepcionista">Recepcionista</option>
            <option value="Administrador">Administrador</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select><br>
        <label>Fecha de Contratación:</label><input type="date" name="Fecha_Contratación" id="editFechaContratacion" required><br>
        <label>Email:</label><input type="email" name="Email" id="editEmail" required><br>
        <label>Teléfono:</label><input type="text" name="Teléfono" id="editTelefono" required><br>
        <label>Departamento:</label>
        <select name="Departamento" id="editDepartamento" required>
            <option value="Recepción">Recepción</option>
            <option value="Administración">Administración</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select><br>
        <input type="submit" name="editar" value="Actualizar Empleado" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="empleados[]"]:checked');
        }

        function editSelected() {
            const checkboxes = document.querySelectorAll('input[name="empleados[]"]:checked');
            if (checkboxes.length === 1) {
                const row = checkboxes[0].closest('tr');
                document.getElementById('editID').value = row.cells[1].innerText;
                document.getElementById('editNombre').value = row.cells[2].innerText;
                document.getElementById('editApellido').value = row.cells[3].innerText;
                document.getElementById('editCargo').value = row.cells[4].innerText;
                document.getElementById('editFechaContratacion').value = row.cells[5].innerText;
                document.getElementById('editEmail').value = row.cells[6].innerText;
                document.getElementById('editTelefono').value = row.cells[7].innerText;
                document.getElementById('editDepartamento').value = row.cells[8].innerText;
                document.getElementById('editForm').classList.remove('hidden');
            } else {
                alert("Por favor selecciona un solo empleado para editar.");
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
