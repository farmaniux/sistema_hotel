<?php
include 'session.php';
include 'db.php';

// Insertar Servicio usando consultas preparadas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $nombre_servicio = $_POST['Nombre_Servicio'];
    $descripcion = $_POST['Descripción'];
    $costo = $_POST['Costo'];
    $horario_disponible = $_POST['Horario_Disponible'];
    $id_reserva = $_POST['ID_Reserva'];

    // Preparar y ejecutar la consulta para insertar el servicio
    $stmt = $conn->prepare("INSERT INTO Servicio (Nombre_Servicio, Descripción, Costo, Horario_Disponible, ID_Reserva) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsi", $nombre_servicio, $descripcion, $costo, $horario_disponible, $id_reserva);

    if ($stmt->execute()) {
        echo "Nuevo servicio registrado exitosamente: $nombre_servicio<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
    $stmt->close();
}

// Eliminar Servicios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['servicios'])) {
        $servicios_a_eliminar = implode(",", $_POST['servicios']);
        echo "Servicios seleccionados para eliminar: $servicios_a_eliminar<br>";

        $sql_delete = "DELETE FROM Servicio WHERE ID_Servicio IN ($servicios_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Servicios eliminados exitosamente.<br>";
        } else {
            echo "Error al eliminar servicios: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ningún servicio para eliminar.<br>";
    }
}

// Editar Servicio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_servicio = $_POST['ID_Servicio'];
    $nombre_servicio = $_POST['Nombre_Servicio'];
    $descripcion = $_POST['Descripción'];
    $costo = $_POST['Costo'];
    $horario_disponible = $_POST['Horario_Disponible'];
    $id_reserva = $_POST['ID_Reserva'];

    $sql_update = "UPDATE Servicio SET Nombre_Servicio=?, Descripción=?, Costo=?, Horario_Disponible=?, ID_Reserva=? WHERE ID_Servicio=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssdsii", $nombre_servicio, $descripcion, $costo, $horario_disponible, $id_reserva, $id_servicio);

    if ($stmt->execute()) {
        echo "Servicio actualizado exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Servicios
$sql = "SELECT * FROM Servicio";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Servicios</title>
    <script>
        function editSelected() {
            const selectedCheckbox = document.querySelector('input[name="servicios[]"]:checked');
            if (selectedCheckbox) {
                const row = selectedCheckbox.closest('tr');
                const cells = row.getElementsByTagName('td');

                document.getElementById('editID').value = cells[1].innerText;
                document.getElementById('editNombreServicio').value = cells[2].innerText;
                document.getElementById('editDescripcion').value = cells[3].innerText;
                document.getElementById('editCosto').value = cells[4].innerText;
                document.getElementById('editHorarioDisponible').value = cells[5].innerText;
                document.getElementById('editIDReserva').value = cells[6].innerText;

                document.getElementById('editForm').classList.remove('hidden');
            }
        }
    </script>
    <style>
        .hidden {
            display: none;
        }
        .boton-actualizar, .boton-editar {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
        }
        .boton-actualizar:hover, .boton-editar:hover {
            background-color: darkblue;
        }
    </style>
</head>
<body>
    <h1>Gestionar Servicios</h1>
    
    <h2>Insertar Nuevo Servicio</h2>
    <form method="post" action="">
        <label>Nombre del Servicio:</label><input type="text" name="Nombre_Servicio" required><br>
        <label>Descripción:</label><input type="text" name="Descripción" required><br>
        <label>Costo:</label><input type="number" step="0.01" name="Costo" required><br>
        <label>Horario Disponible:</label><input type="text" name="Horario_Disponible" required><br>
        <label>ID Reserva:</label><input type="number" name="ID_Reserva"><br>
        <input type="submit" name="insertar" value="Insertar Servicio">
    </form>

    <h2>Servicios Registrados</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Servicio</th>
                <th>Nombre del Servicio</th>
                <th>Descripción</th>
                <th>Costo</th>
                <th>Horario Disponible</th>
                <th>ID Reserva</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='servicios[]' value='" . $row["ID_Servicio"] . "'></td>";
                    echo "<td>" . $row["ID_Servicio"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["Nombre_Servicio"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Descripción"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Costo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Horario_Disponible"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["ID_Reserva"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay servicios registrados</td></tr>";
            }
            ?>
        </table>
        <input type="submit" name="eliminar" value="Eliminar Servicios">
        <button type="button" onclick="editSelected()" class="boton-editar">Editar Servicio</button>
    </form>

    <h2>Editar Servicio</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Servicio" id="editID">
        <label>Nombre del Servicio:</label><input type="text" name="Nombre_Servicio" id="editNombreServicio" required><br>
        <label>Descripción:</label><input type="text" name="Descripción" id="editDescripcion" required><br>
        <label>Costo:</label><input type="number" step="0.01" name="Costo" id="editCosto" required><br>
        <label>Horario Disponible:</label><input type="text" name="Horario_Disponible" id="editHorarioDisponible" required><br>
        <label>ID Reserva:</label><input type="number" name="ID_Reserva" id="editIDReserva"><br>

        <input type="submit" name="editar" value="Actualizar Servicio" class="boton-actualizar">
    </form>

</body>
</html>

<?php
$conn->close();
?>
