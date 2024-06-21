<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../scripts/conexion.php'; // Ajustar la ruta al archivo de conexión

    // Recibir datos del formulario
    $email = $mysqli->real_escape_string($_POST['email']);
    $contrasena = $mysqli->real_escape_string($_POST['contrasena']);
    $ubicacion_lat = isset($_POST['ubicacion_lat']) ? floatval($_POST['ubicacion_lat']) : null;
    $ubicacion_long = isset($_POST['ubicacion_long']) ? floatval($_POST['ubicacion_long']) : null;

    // Buscar usuario en la base de datos
    $sql = "SELECT * FROM Usuarios WHERE email='$email'";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verificar la contraseña
        if (password_verify($contrasena, $user['Contrasena'])) {
            // Verificar si se obtuvieron las coordenadas antes de actualizar
            if ($ubicacion_lat !== null && $ubicacion_long !== null) {
                // Actualizar coordenadas del usuario
                $sql_update = "UPDATE Usuarios SET ubicacion_lat='$ubicacion_lat', ubicacion_long='$ubicacion_long' WHERE email='$email'";
                if ($mysqli->query($sql_update) === FALSE) {
                    die("Error actualizando coordenadas: " . $mysqli->error);
                }
            }

            // Iniciar sesión
            $_SESSION['usuario_id'] = $user['Id_usuario'];
            $_SESSION['nombre'] = $user['Nombre'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
            $_SESSION["autentificado"] = "SI";


            // Consulta para obtener los eventos a los que el usuario está apuntado
            $query_eventos = "SELECT e.Nombre, e.Fecha FROM Eventos e INNER JOIN Asistencia a ON e.id_evento = a.id_evento WHERE a.id_usuario = ?";
            $stmt_eventos = $mysqli->prepare($query_eventos);
            $stmt_eventos->bind_param("i", $_SESSION['usuario_id']);
            $stmt_eventos->execute();
            $result_eventos = $stmt_eventos->get_result();

            // Almacenar eventos del usuario en una variable de sesión
            $eventos_usuario = [];
            while ($row = $result_eventos->fetch_assoc()) {
                $eventos_usuario[] = $row;
            }
            $_SESSION['eventos_usuario'] = $eventos_usuario;

            // Verificar si las variables de sesión están configuradas
            if (isset($_SESSION['usuario_id']) && isset($_SESSION['nombre']) && $_SESSION["autentificado"] == "SI") {
                // Redirigir a la página principal o panel de usuario
                header("Location: ../index.php"); // Ajustar esta ruta según tu estructura
                exit();
            } else {
                echo "Error al iniciar sesión. Inténtelo de nuevo.";
            }
        } else {
            header("Location: ../Inicio/inicioo.php?errorusuario=SI"); // Ajustar esta ruta según tu estructura
            exit();
        }
    } else {
        header("Location: ../Inicio/inicioo.php?errorusuario=SI"); // Ajustar esta ruta según tu estructura
        exit();
    }

    // Cerrar conexión
    $mysqli->close();
} else {
    echo "Método de solicitud no válido";
}
?>
