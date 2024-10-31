<?php
include 'session.php';
include 'db.php';

// Insertar Tipo de Usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertar'])) {
    $descripcion = $_POST['Descripción'];

    $sql = "INSERT INTO Tipo_Usuario (Descripción) 
            VALUES ('$descripcion')";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo tipo de usuario registrado exitosamente<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Eliminar Tipos de Usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!empty($_POST['tipos_usuario'])) {
        $tipos_usuario_a_eliminar = implode(",", $_POST['tipos_usuario']);
        echo "Tipos de usuario seleccionados para eliminar: $tipos_usuario_a_eliminar<br>";

        $sql_delete = "DELETE FROM Tipo_Usuario WHERE ID_Tipo_Usuario IN ($tipos_usuario_a_eliminar)";
        if ($conn->query($sql_delete) === TRUE) {
            echo "Tipos de usuario eliminados exitosamente.<br>";
        } else {
            echo "Error al eliminar tipos de usuario: " . $conn->error . "<br>";
        }
    } else {
        echo "No se seleccionó ningún tipo de usuario para eliminar.<br>";
    }
}

// Editar Tipo de Usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    $id_tipo_usuario = $_POST['ID_Tipo_Usuario'];
    $descripcion = $_POST['Descripción'];

    $sql_update = "UPDATE Tipo_Usuario SET Descripción='$descripcion' WHERE ID_Tipo_Usuario='$id_tipo_usuario'";
    
    if ($conn->query($sql_update) === TRUE) {
        echo "Tipo de usuario actualizado exitosamente.<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Mostrar Tipos de Usuario
$sql = "SELECT * FROM Tipo_Usuario";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Tipos de Usuario</title>
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
    <h1>Gestionar Tipos de Usuario</h1>
    
    <h2>Insertar Nuevo Tipo de Usuario</h2>
    <form method="post" action="">
        <label>Descripción:</label><input type="text" name="Descripción" required><br>
        <input type="submit" name="insertar" value="Insertar" class="boton-actualizar">
    </form>

    <h2>Tipos de Usuario Registrados</h2>
    <form method="post" action="">
        <table border="1">
            <tr>
                <th>Seleccionar</th>
                <th>ID Tipo Usuario</th>
                <th>Descripción</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='tipos_usuario[]' value='" . $row["ID_Tipo_Usuario"] . "' onclick='toggleEditButton(this)'></td>";
                    echo "<td>" . $row["ID_Tipo_Usuario"] . "</td>";
                    echo "<td>" . $row["Descripción"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No hay tipos de usuario registrados</td></tr>";
            }
            ?>
        </table>
        <br>
        <input type="submit" name="eliminar" value="Eliminar Tipos de Usuario" class="boton-actualizar">
        <input type="button" id="editButton" value="Editar Tipo de Usuario" onclick="editSelected()" class="boton-actualizar" disabled>
    </form>

    <!-- Formulario para Editar Tipo de Usuario -->
    <h2>Editar Tipo de Usuario</h2>
    <form method="post" action="" id="editForm" class="hidden">
        <input type="hidden" name="ID_Tipo_Usuario" id="editID" required>
        <label>Descripción:</label><input type="text" name="Descripción" id="editDescripcion" required><br>
        <input type="submit" name="editar" value="Actualizar Tipo de Usuario" class="boton-actualizar">
    </form>

    <script>
        function toggleEditButton(checkbox) {
            const editButton = document.getElementById('editButton');
            editButton.disabled = !document.querySelector('input[name="tipos_usuario[]"]:checked');
        }

        function editSelected() {
            const checkboxes = document.querySelectorAll('input[name="tipos_usuario[]"]:checked');
            if (checkboxes.length === 1) {
                const row = checkboxes[0].closest('tr');
                document.getElementById('editID').value = row.cells[1].innerText;
                document.getElementById('editDescripcion').value = row.cells[2].innerText;
                document.getElementById('editForm').classList.remove('hidden');
            } else {
                alert("Por favor selecciona un solo tipo de usuario para editar.");
            }
        }
    </script>

</body>
</html>

<?php
$conn->close();
?>
