<?php
session_start();
include '../scripts/conexion.php';

// Verificar si se recibieron eventos seleccionados y el id_discoteca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventos_seleccionados']) && isset($_POST['id_discoteca'])) {
    
    $id_discoteca = $_POST['id_discoteca'];
    $eventos_seleccionados = $_POST['eventos_seleccionados'];

    $usuario_id = $_SESSION['usuario_id']; 

    // Obtener la fecha actual para insertarla en la tabla Asistencia
    $fecha = date('Y-m-d H:i:s');

    // Inicializar una variable para contar eventos ya apuntados
    $already_signed_up = false;

    // Preparar la consulta para insertar en la tabla Asistencia
    $sql = "INSERT INTO Asistencia (Id_discoteca, Fecha, Id_usuario, Id_evento) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        echo "Error en la preparación de la consulta: " . $mysqli->error;
        exit();
    }

    foreach ($eventos_seleccionados as $id_evento) {
        // Verificar si el usuario ya está apuntado a este evento
        $check_sql = "SELECT * FROM Asistencia WHERE Id_usuario = ? AND Id_evento = ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("ii", $usuario_id, $id_evento);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $already_signed_up = true;
            continue;
        }

        $check_stmt->close();

        // Si no está apuntado, proceder con la inserción
        $stmt->bind_param("issi", $id_discoteca, $fecha, $usuario_id, $id_evento);
        if (!$stmt->execute()) {
            echo "Error al ejecutar la consulta: " . $stmt->error;
            exit();
        }
    }

    $stmt->close();
    $mysqli->close();

    if ($already_signed_up) {
        echo "<script>
                alert('No puedes apuntarte a eventos a los que ya estás apuntado.');
                window.location.href = '../index.php';
              </script>";
    } else {
        header("Location: ../index.php");
    }
    exit();
} else {
    echo "Error: No se recibieron eventos seleccionados o id_discoteca.";
}
?>
