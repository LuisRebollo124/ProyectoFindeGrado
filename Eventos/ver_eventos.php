<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to bottom, #b19cd9, #d8bfd8); 
            margin: 0;
        }
        a {
            text-decoration: none;
            color: white;
        }
        button {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            background-color: #9370db;
            color: white;
            font-family: Arial, sans-serif;
            cursor: pointer;
        }

        button:hover {
            background-color: #7a50b9;
        }

        #volver {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            background-color: red;
            color: white;
            font-family: Arial, sans-serif;
            cursor: pointer;
        }

        #volver:hover {
            background-color: #9c0720;
        }
    </style>
</head>
<body>
    <form action="../Asistencia/apuntarse_evento.php" method="POST">
        <?php
        session_start();
        include '../scripts/conexion.php';

        // Obtener id_discoteca de la URL
        $id_discoteca = $_GET['id_discoteca'];
        $usuario_id = $_SESSION['usuario_id'];

        // Obtener la fecha actual
        $fecha_actual = date('Y-m-d');

        // Consulta para obtener los eventos de la discoteca que son posteriores a la fecha actual
        $query = "SELECT e.id_evento, e.Nombre AS Nombre_evento, e.Descripción, e.Fecha, e.Asistencia, 
                         COUNT(a.id_evento) AS num_asistentes, 
                         GROUP_CONCAT(u.Nombre, ' ', u.Apellidos SEPARATOR ', ') AS nombres_usuarios 
                  FROM Eventos e 
                  LEFT JOIN Asistencia a ON e.id_evento = a.id_evento 
                  LEFT JOIN Usuarios u ON a.id_usuario = u.id_usuario 
                  WHERE e.Id_discoteca = ? AND e.Fecha >= ? 
                  GROUP BY e.id_evento, e.Nombre, e.Descripción, e.Fecha, e.Asistencia";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("is", $id_discoteca, $fecha_actual);
        $stmt->execute();
        $stmt->store_result();

        // Verificar si hay eventos disponibles
        $num_rows = $stmt->num_rows;

        if ($num_rows > 0) {
            // Mostrar eventos
            echo "<h1>Eventos disponibles:</h1>";
            echo "<ul>";

            $stmt->bind_result($id_evento, $nombre_evento, $descripcion, $fecha, $asistencia, $num_asistentes, $nombres_usuarios);
            while ($stmt->fetch()) {
                echo "<li>";
                echo "<label>";
                if ($num_asistentes >= $asistencia) {
                    echo "<input type='checkbox' name='eventos_seleccionados[]' value='$id_evento' disabled> ";
                    echo "<strong>$nombre_evento</strong>: $descripcion <br> - Fecha: $fecha <br> Aforo máximo: $asistencia personas <br> Estado: Evento lleno";
                } else {
                    echo "<input type='checkbox' name='eventos_seleccionados[]' value='$id_evento'> ";
                    echo "<strong>$nombre_evento</strong>: $descripcion <br> - Fecha: $fecha <br> Aforo máximo: $asistencia personas <br> Estado: Disponible";
                }
                if (!empty($nombres_usuarios)) {
                    echo "<br> Registrados: $nombres_usuarios";
                }
                echo "</label>";
                echo "</li>";
            }
            echo "</ul>";

            // Botón para apuntarse a eventos seleccionados
            echo '<button type="submit">Apuntarse a Eventos</button> ';
            echo '<br>';
        } else {
            echo "<h2>No hay eventos disponibles en esta discoteca o todos los eventos son pasados.</h2>";
        }

        // Campo oculto para enviar el id_discoteca junto con el formulario
        echo '<input type="hidden" name="id_discoteca" value="' . $id_discoteca . '">';

        // Botón para volver al index.php
        echo '<br> <a href="../index.php" id="volver">Volver</a>';

        // Cerrar la conexión y liberar recursos
        $stmt->close();
        $mysqli->close();
        ?>
    </form>
</body>
</html>
