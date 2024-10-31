<?php
include 'db.php';

// Función para sanitizar entradas
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Insertar Reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $fecha_inicio = sanitizeInput($_POST['Fecha_Inicio']);
    $fecha_fin = sanitizeInput($_POST['Fecha_Fin']);
    $estado = sanitizeInput($_POST['Estado']);
    $cantidad_personas = sanitizeInput($_POST['Cantidad_Personas']);
    $id_cliente = sanitizeInput($_POST['ID_Cliente']);
    $id_habitacion = sanitizeInput($_POST['ID_Habitación']);
    $metodo_pago = sanitizeInput($_POST['Metodo_Pago']);
    $numero_transaccion = !empty($_POST['Numero_Transaccion']) ? sanitizeInput($_POST['Numero_Transaccion']) : null;

    $sql = "INSERT INTO Reserva (Fecha_Inicio, Fecha_Fin, Estado, Cantidad_Personas, ID_Cliente, ID_Habitación, Metodo_Pago, Numero_Transaccion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiisss", $fecha_inicio, $fecha_fin, $estado, $cantidad_personas, $id_cliente, $id_habitacion, $metodo_pago, $numero_transaccion);

    if ($stmt->execute()) {
        echo "Nueva reserva registrada exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Eliminar Reservas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['reservas'])) {
        $reservas_a_eliminar = implode(",", $_POST['reservas']);
        echo "Reservas seleccionadas para eliminar: $reservas_a_eliminar<br>";

        $sql_delete = "DELETE FROM Reserva WHERE ID_Reserva IN ($reservas_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Reservas eliminadas exitosamente.<br>";
        } else {
            echo "Error al eliminar reservas: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ninguna reserva para eliminar.<br>";
    }
}

// Editar Reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_reserva = sanitizeInput($_POST['ID_Reserva']);
    $fecha_inicio = sanitizeInput($_POST['Fecha_Inicio']);
    $fecha_fin = sanitizeInput($_POST['Fecha_Fin']);
    $estado = sanitizeInput($_POST['Estado']);
    $cantidad_personas = sanitizeInput($_POST['Cantidad_Personas']);
    $id_cliente = sanitizeInput($_POST['ID_Cliente']);
    $id_habitacion = sanitizeInput($_POST['ID_Habitación']);
    $metodo_pago = sanitizeInput($_POST['Metodo_Pago']);
    $numero_transaccion = !empty($_POST['Numero_Transaccion']) ? sanitizeInput($_POST['Numero_Transaccion']) : null;

    $sql_update = "UPDATE Reserva SET Fecha_Inicio=?, Fecha_Fin=?, Estado=?, Cantidad_Personas=?, ID_Cliente=?, ID_Habitación=?, Metodo_Pago=?, Numero_Transaccion=? WHERE ID_Reserva=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssiissssi", $fecha_inicio, $fecha_fin, $estado, $cantidad_personas, $id_cliente, $id_habitacion, $metodo_pago, $numero_transaccion, $id_reserva);

    if ($stmt->execute()) {
        echo "Reserva actualizada exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Reservas
$sql = "SELECT * FROM Reserva";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservas</title>
    <script>
        function toggleTransactionField() {
            const metodoPago = document.getElementById('metodo_pago').value;
            const numeroTransaccion = document.getElementById('numero_transaccion');

            numeroTransaccion.disabled = metodoPago === 'Efectivo';
            if (metodoPago === 'Efectivo') {
                numeroTransaccion.value = '';
            }
        }

        function editSelected() {
            const selectedCheckbox = document.querySelector('input[name="reservas[]"]:checked');
            if (selectedCheckbox) {
                const row = selectedCheckbox.closest('tr');
                const cells = row.getElementsByTagName('td');

                document.getElementById('editID').value = cells[1].innerText;
                document.getElementById('editFechaInicio').value = cells[2].innerText;
                document.getElementById('editFechaFin').value = cells[3].innerText;
                document.getElementById('editEstado').value = cells[4].innerText;
                document.getElementById('editCantidadPersonas').value = cells[5].innerText;
                document.getElementById('editIDCliente').value = cells[6].innerText;
                document.getElementById('editIDHabitacion').value = cells[7].innerText;
                document.getElementById('editMetodoPago').value = cells[8].innerText;
                document.getElementById('editNumeroTransaccion').value = cells[9].innerText;

                document.getElementById('editForm').classList.remove('hidden');
            }
        }
    </script>
    <style>
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
    <h1>Gestionar Reservas</h1>
    
    <h2>Insertar Nueva Reserva</h2>
    <form method="post" action="">
        <label>Fecha de Inicio:</label><input type="date" name="Fecha_Inicio" required><br>
        <label>Fecha de Fin:</label><input type="date" name="Fecha_Fin" required><br>
        <label>Estado:</label>
        <select name="Estado" required>
            <option value="Confirmada">Confirmada</option>
            <option value="Cancelada">Cancelada</option>
            <option value="Pendiente">Pendiente</option>
        </select><br>
        <label>Cantidad de Personas:</label><input type="number" name="Cantidad_Personas" required><br>
        <label>ID Cliente:</label><input type="number" name="ID_Cliente" required><br>
        <label>ID Habitación:</label><input type="number" name="ID_Habitación" required><br>

        <label>Método de Pago:</label>
        <select name="Metodo_Pago" id="metodo_pago" onchange="toggleTransactionField()" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
        </select><br>

        <label>Número de Transacción:</label>
        <input type="text" name="Numero_Transaccion" id="numero_transaccion" disabled><br>

        <input type="submit" name="insertar" value="Insertar Reserva">
    </form>

    <h2>Reservas Registradas</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Reserva</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Fin</th>
                <th>Estado</th>
                <th>Cantidad de Personas</th>
                <th>ID Cliente</th>
                <th>ID Habitación</th>
                <th>Método de Pago</th>
                <th>Número de Transacción</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='reservas[]' value='" . $row["ID_Reserva"] . "'></td>";
                    echo "<td>" . $row["ID_Reserva"] . "</td>";
                    echo "<td>" . $row["Fecha_Inicio"] . "</td>";
                    echo "<td>" . $row["Fecha_Fin"] . "</td>";
                    echo "<td>" . $row["Estado"] . "</td>";
                    echo "<td>" . $row["Cantidad_Personas"] . "</td>";
                    echo "<td>" . $row["ID_Cliente"] . "</td>";
                    echo "<td>" . $row["ID_Habitación"] . "</td>";
                    echo "<td>" . $row["Metodo_Pago"] . "</td>";
                    echo "<td>" . $row["Numero_Transaccion"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No hay reservas registradas</td></tr>";
            }
            ?>
        </table>
        <input type="submit" name="eliminar" value="Eliminar Reservas" class="boton-actualizar">
        <button type="button" onclick="editSelected()" class="boton-actualizar">Editar Reserva</button>
    </form>

    <h2>Editar Reserva</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Reserva" id="editID">
        <label>Fecha de Inicio:</label><input type="date" name="Fecha_Inicio" id="editFechaInicio" required><br>
        <label>Fecha de Fin:</label><input type="date" name="Fecha_Fin" id="editFechaFin" required><br>
        <label>Estado:</label>
        <select name="Estado" id="editEstado" required>
            <option value="Confirmada">Confirmada</option>
            <option value="Cancelada">Cancelada</option>
            <option value="Pendiente">Pendiente</option>
        </select><br>
        <label>Cantidad de Personas:</label><input type="number" name="Cantidad_Personas" id="editCantidadPersonas" required><br>
        <label>ID Cliente:</label><input type="number" name="ID_Cliente" id="editIDCliente" required><br>
        <label>ID Habitación:</label><input type="number" name="ID_Habitación" id="editIDHabitacion" required><br>

        <label>Método de Pago:</label>
        <select name="Metodo_Pago" id="editMetodoPago" onchange="toggleTransactionField()" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
        </select><br>

        <label>Número de Transacción:</label>
        <input type="text" name="Numero_Transaccion" id="editNumeroTransaccion"><br>

        <input type="submit" name="editar" value="Actualizar Reserva" class="boton-actualizar">
    </form>

</body>
</html>

<?php
$conn->close();
?>
