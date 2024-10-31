<?php
include 'session.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['clientes'])) {
        $clientes_a_eliminar = implode(",", $_POST['clientes']);
        echo "Clientes seleccionados para eliminar: $clientes_a_eliminar<br>";

        $sql_delete = "DELETE FROM Cliente WHERE ID_Cliente IN ($clientes_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Clientes eliminados exitosamente.<br>";
        } else {
            echo "Error al eliminar clientes: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ningún cliente para eliminar.<br>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $nombre = $_POST['Nombre'];
    $apellido = $_POST['Apellido'];
    $email = $_POST['Email'];
    $telefono = $_POST['Telefono'];
    $direccion = $_POST['Direccion'];
    $fecha_nacimiento = $_POST['Fecha_Nacimiento'];
    $genero = $_POST['Genero'];
    $nacionalidad = $_POST['Nacionalidad'];
    $tipo_cliente = $_POST['Tipo_Cliente'];
    $fecha_registro = $_POST['Fecha_Registro'];

    $sql = "INSERT INTO Cliente (Nombre, Apellido, Email, Teléfono, Dirección, Fecha_Nacimiento, Género, Nacionalidad, Tipo_Cliente, Fecha_Registro) 
            VALUES ('$nombre', '$apellido', '$email', '$telefono', '$direccion', '$fecha_nacimiento', '$genero', '$nacionalidad', '$tipo_cliente', '$fecha_registro')";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo cliente registrado exitosamente<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_cliente = $_POST['ID_Cliente'];
    $nombre = $_POST['Nombre'];
    $apellido = $_POST['Apellido'];
    $email = $_POST['Email'];
    $telefono = $_POST['Telefono'];
    $direccion = $_POST['Direccion'];
    $fecha_nacimiento = $_POST['Fecha_Nacimiento'];
    $genero = $_POST['Genero'];
    $nacionalidad = $_POST['Nacionalidad'];
    $tipo_cliente = $_POST['Tipo_Cliente'];
    $fecha_registro = $_POST['Fecha_Registro'];

    $sql_update = "UPDATE Cliente SET Nombre='$nombre', Apellido='$apellido', Email='$email', Teléfono='$telefono', Dirección='$direccion', 
                   Fecha_Nacimiento='$fecha_nacimiento', Género='$genero', Nacionalidad='$nacionalidad', Tipo_Cliente='$tipo_cliente', 
                   Fecha_Registro='$fecha_registro' WHERE ID_Cliente='$id_cliente'";

    if ($conn->query($sql_update) === TRUE) {
        echo "Cliente actualizado exitosamente.<br>";
    } else {
        echo "Error al actualizar cliente: " . $conn->error . "<br>";
    }
}

$sql = "SELECT * FROM Cliente";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
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
        }
    </style>
</head>
<body>
    <h1>Gestionar Clientes</h1>

    <!-- Formulario de Inserción -->
    <h2>Insertar Nuevo Cliente</h2>
    <form method="post" action="">
        <label>Nombre:</label><input type="text" name="Nombre" required><br>
        <label>Apellido:</label><input type="text" name="Apellido" required><br>
        <label>Email:</label><input type="email" name="Email" required><br>
        <label>Teléfono:</label><input type="text" name="Telefono" required><br>
        <label>Dirección:</label><input type="text" name="Direccion" required><br>
        <label>Fecha de Nacimiento:</label><input type="date" name="Fecha_Nacimiento" required><br>
        <label>Género:</label>
        <select name="Genero" required>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select><br>
        <label>Nacionalidad:</label><input type="text" name="Nacionalidad" required><br>
        <label>Tipo de Cliente:</label>
        <select name="Tipo_Cliente" required>
            <option value="Frecuente">Frecuente</option>
            <option value="Nuevo">Nuevo</option>
            <option value="VIP">VIP</option>
        </select><br>
        <label>Fecha de Registro:</label><input type="date" name="Fecha_Registro" required><br>
        <input type="submit" name="insertar" value="Insertar Cliente">
    </form>

    <!-- Formulario para Eliminar Clientes -->
    <form method="post" action="">
        <h2>Clientes Registrados</h2>
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Cliente</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Fecha de Nacimiento</th>
                <th>Género</th>
                <th>Nacionalidad</th>
                <th>Tipo de Cliente</th>
                <th>Fecha de Registro</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='clientes[]' value='" . $row["ID_Cliente"] . "' onclick='toggleEditButton(this)'></td>"; // Casilla de selección
                    echo "<td>" . $row["ID_Cliente"] . "</td>";
                    echo "<td>" . $row["Nombre"] . "</td>";
                    echo "<td>" . $row["Apellido"] . "</td>";
                    echo "<td>" . $row["Email"] . "</td>";
                    echo "<td>" . $row["Teléfono"] . "</td>";
                    echo "<td>" . $row["Dirección"] . "</td>";
                    echo "<td>" . $row["Fecha_Nacimiento"] . "</td>";
                    echo "<td>" . $row["Género"] . "</td>";
                    echo "<td>" . $row["Nacionalidad"] . "</td>";
                    echo "<td>" . $row["Tipo_Cliente"] . "</td>";
                    echo "<td>" . $row["Fecha_Registro"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No hay clientes registrados</td></tr>";
            }
            ?>
        </table>
        <br>
        <input type="submit" name="eliminar" value="Eliminar Cliente">
        <input type="button" id="editButton" value="Editar Cliente" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <!-- Formulario para Editar Cliente -->
    <h2>Editar Cliente</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Cliente" id="editID" required>
        <label>Nombre:</label><input type="text" name="Nombre" id="editNombre" required><br>
        <label>Apellido:</label><input type="text" name="Apellido" id="editApellido" required><br>
        <label>Email:</label><input type="email" name="Email" id="editEmail" required><br>
        <label>Teléfono:</label><input type="text" name="Telefono" id="editTelefono" required><br>
        <label>Dirección:</label><input type="text" name="Direccion" id="editDireccion" required><br>
        <label>Fecha de Nacimiento:</label><input type="date" name="Fecha_Nacimiento" id="editFechaNacimiento" required><br>
        <label>Género:</label>
        <select name="Genero" id="editGenero" required>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select><br>
        <label>Nacionalidad:</label><input type="text" name="Nacionalidad" id="editNacionalidad" required><br>
        <label>Tipo de Cliente:</label>
        <select name="Tipo_Cliente" id="editTipoCliente" required>
            <option value="Frecuente">Frecuente</option>
            <option value="Nuevo">Nuevo</option>
            <option value="VIP">VIP</option>
        </select><br>
        <label>Fecha de Registro:</label><input type="date" name="Fecha_Registro" id="editFechaRegistro" required><br>
        <input type="submit" name="editar" value="Actualizar Cliente" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="clientes[]"]:checked');
        }

        function editSelected() {
            const selectedCheckbox = document.querySelector('input[name="clientes[]"]:checked');
            if (selectedCheckbox) {
                const row = selectedCheckbox.closest('tr');
                const cells = row.getElementsByTagName('td');

                document.getElementById('editID').value = cells[1].innerText;
                document.getElementById('editNombre').value = cells[2].innerText;
                document.getElementById('editApellido').value = cells[3].innerText;
                document.getElementById('editEmail').value = cells[4].innerText;
                document.getElementById('editTelefono').value = cells[5].innerText;
                document.getElementById('editDireccion').value = cells[6].innerText;
                document.getElementById('editFechaNacimiento').value = cells[7].innerText;
                document.getElementById('editGenero').value = cells[8].innerText;
                document.getElementById('editNacionalidad').value = cells[9].innerText;
                document.getElementById('editTipoCliente').value = cells[10].innerText;
                document.getElementById('editFechaRegistro').value = cells[11].innerText;

                document.getElementById('editForm').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
