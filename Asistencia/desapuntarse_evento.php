<?php
session_start();
include '../scripts/conexion.php';

// Verificar si se recibieron eventos seleccionados a través de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos de la solicitud POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['eventos_seleccionados']) && is_array($input['eventos_seleccionados'])) {
        $eventos_seleccionados = $input['eventos_seleccionados'];
        $usuario_id = $_SESSION['usuario_id']; // Obtener Id_usuario desde la sesión

        // Verificar si eventos_seleccionados es un array y no está vacío
        if (!empty($eventos_seleccionados)) {
            $placeholders = implode(",", array_fill(0, count($eventos_seleccionados), "?"));
            $types = str_repeat("i", count($eventos_seleccionados) + 1);
            $params = array_merge([$usuario_id], $eventos_seleccionados);

            $sql = "DELETE FROM Asistencia WHERE Id_usuario = ? AND Id_evento IN ($placeholders)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                echo json_encode(["success" => false, "message" => "Error en la preparación de la consulta: " . $mysqli->error]);
                exit();
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            if ($stmt->errno) {
                echo json_encode(["success" => false, "message" => "Error al ejecutar la consulta: " . $stmt->error]);
                exit();
            }

            // Actualizar la sesión eliminando los eventos seleccionados
            $_SESSION['eventos_usuario'] = array_filter($_SESSION['eventos_usuario'], function($evento) use ($eventos_seleccionados) {
                return !in_array($evento['Id_evento'], $eventos_seleccionados);
            });
            $stmt->close();

            echo json_encode(["success" => true]);
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "No se recibieron eventos seleccionados válidos."]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "No se recibieron eventos seleccionados válidos."]);
        exit();
    }
} else {
    http_response_code(405);
    header("Allow: POST"); // Indicar que solo se permite el método POST
    echo json_encode(["success" => false, "message" => "Método de solicitud no permitido"]);
    exit();
}
?>
