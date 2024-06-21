<?php
include '../scripts/conexion.php';

$query = "SELECT d.Nombre, d.Direccion, d.Ubicacion_lat, d.Ubicacion_long, d.id_usuario, u.tipo_usuario,
                 e.Id_evento AS id_evento, e.Nombre AS NombreEvento, e.Descripción, e.Asistencia
          FROM Discotecas d
          INNER JOIN Usuarios u ON d.id_usuario = u.id_usuario
          LEFT JOIN Eventos e ON d.id_usuario = e.Id_discoteca";

$result = $mysqli->query($query);

$discotecas = array();
while ($row = $result->fetch_assoc()) {
    $discotecaId = $row['id_usuario'];

    // Verificar si ya existe la discoteca en el array
    if (!isset($discotecas[$discotecaId])) {
        $discotecas[$discotecaId] = [
            'id_usuario' => $discotecaId,
            'Nombre' => $row['Nombre'],
            'Direccion' => $row['Direccion'],
            'Ubicacion_lat' => $row['Ubicacion_lat'],
            'Ubicacion_long' => $row['Ubicacion_long'],
            'tipo_usuario' => $row['tipo_usuario'],
            'eventos' => [] // Inicializamos eventos como un array vacío
        ];
    }

    // Agregar evento si existe para esta discoteca
    if ($row['id_evento']) {
        $discotecas[$discotecaId]['eventos'][] = [
            'id' => $row['id_evento'],
            'Nombre' => $row['NombreEvento'],
            'Descripción' => $row['Descripción'],
            'Asistencia' => $row['Asistencia']
        ];
    }
}

echo json_encode(array_values($discotecas));

$mysqli->close();
?>
