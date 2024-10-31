<?php
include 'session.php';
include 'db.php';

// Insertar Factura
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $fecha_emision = $_POST['Fecha_Emisión'];
    $monto_total = $_POST['Monto_Total'];
    $descripcion_detallada = $_POST['Descripción_Detallada'];
    $id_cliente = $_POST['ID_Cliente'];

    $sql = "INSERT INTO Factura (Fecha_Emisión, Monto_Total, Descripción_Detallada, ID_Cliente) 
            VALUES ('$fecha_emision', '$monto_total', '$descripcion_detallada', '$id_cliente')";

    if ($conn->query($sql) === TRUE) {
        echo "Nueva factura registrada exitosamente<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Eliminar Factura
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['facturas'])) {
        // Convertimos el array de IDs en una lista para la consulta SQL
        $facturas_a_eliminar = implode(",", $_POST['facturas']);

        $sql_delete = "DELETE FROM Factura WHERE ID_Factura IN ($facturas_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Facturas eliminadas exitosamente.<br>";
        } else {
            echo "Error al eliminar facturas: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ninguna factura para eliminar.<br>";
    }
}

// Editar Factura
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_factura = $_POST['ID_Factura'];
    $fecha_emision = $_POST['Fecha_Emisión'];
    $monto_total = $_POST['Monto_Total'];
    $descripcion_detallada = $_POST['Descripción_Detallada'];
    $id_cliente = $_POST['ID_Cliente'];

    $sql_update = "UPDATE Factura SET Fecha_Emisión=?, Monto_Total=?, Descripción_Detallada=?, ID_Cliente=? WHERE ID_Factura=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sdssi", $fecha_emision, $monto_total, $descripcion_detallada, $id_cliente, $id_factura);

    if ($stmt->execute()) {
        echo "Factura actualizada exitosamente.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Mostrar Facturas
$sql = "SELECT * FROM Factura";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Facturas</title>
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
    <h1>Gestionar Facturas</h1>
    
    <h2>Insertar Nueva Factura</h2>
    <form method="post" action="">
        <label>Fecha de Emisión:</label><input type="date" name="Fecha_Emisión" required><br>
        <label>Monto Total:</label><input type="number" step="0.01" name="Monto_Total" required><br>
        <label>Descripción Detallada:</label><input type="text" name="Descripción_Detallada" required><br>
        <label>ID Cliente:</label><input type="number" name="ID_Cliente" required><br>
        <input type="submit" name="insertar" value="Insertar" class="boton-actualizar">
    </form>

    <h2>Facturas Registradas</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Factura</th>
                <th>Fecha Emisión</th>
                <th>Monto Total</th>
                <th>Descripción Detallada</th>
                <th>ID Cliente</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='facturas[]' value='" . $row["ID_Factura"] . "' onclick='toggleEditButton(this)'></td>"; // Casilla de selección
                    echo "<td>" . $row["ID_Factura"] . "</td>";
                    echo "<td>" . $row["Fecha_Emisión"] . "</td>";
                    echo "<td>" . $row["Monto_Total"] . "</td>";
                    echo "<td>" . $row["Descripción_Detallada"] . "</td>";
                    echo "<td>" . $row["ID_Cliente"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay facturas registradas</td></tr>";
            }
            ?>
        </table>
        <br>
        <input type="submit" name="eliminar" value="Eliminar Factura" class="boton-actualizar">
        <input type="button" id="editButton" value="Editar Factura" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <!-- Formulario para Editar Factura -->
    <h2>Editar Factura</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Factura" id="editID" required>
        <label>Fecha de Emisión:</label><input type="date" name="Fecha_Emisión" id="editFechaEmision" required><br>
        <label>Monto Total:</label><input type="number" step="0.01" name="Monto_Total" id="editMontoTotal" required><br>
        <label>Descripción Detallada:</label><input type="text" name="Descripción_Detallada" id="editDescripcionDetallada" required><br>
        <label>ID Cliente:</label><input type="number" name="ID_Cliente" id="editIDCliente" required><br>
        <input type="submit" name="editar" value="Actualizar Factura" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="facturas[]"]:checked');
        }

        function editSelected() {
            const checkboxes = document.querySelectorAll('input[name="facturas[]"]:checked');
            if (checkboxes.length === 1) {
                const row = checkboxes[0].closest('tr');
                document.getElementById('editID').value = row.cells[1].innerText;
                document.getElementById('editFechaEmision').value = row.cells[2].innerText;
                document.getElementById('editMontoTotal').value = row.cells[3].innerText;
                document.getElementById('editDescripcionDetallada').value = row.cells[4].innerText;
                document.getElementById('editIDCliente').value = row.cells[5].innerText;
                document.getElementById('editForm').classList.remove('hidden');
            } else {
                alert("Por favor selecciona una sola factura para editar.");
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
