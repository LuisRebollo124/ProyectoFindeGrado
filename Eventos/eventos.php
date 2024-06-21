<?php
include('../scripts/conexion.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_discoteca = $_POST['id_discoteca'];  
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $aforo = $_POST['aforo'];

    // Insertar el nuevo evento en la base de datos
    $sql = "INSERT INTO Eventos (Nombre, Descripción, Fecha, Asistencia, Id_discoteca) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        echo "Error en la preparación de la consulta: " . $mysqli->error;
        exit();
    }
    $stmt->bind_param("ssssi", $nombre, $descripcion, $fecha, $aforo, $id_discoteca);
    if ($stmt->execute()) {
        header("Location: ../index.php");
        exit();
    } else {
        echo "Error al ejecutar la consulta: " . $stmt->error;
    }
    $stmt->close();
    $mysqli->close();
} else {
    echo "Error: Se esperaba una solicitud POST.";
}
?>
