<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../scripts/conexion.php'; // Incluir el archivo de conexión

    // Recibir datos del formulario
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $genero = $_POST['genero'];
    $fecha_nac = $_POST['fecha_nac'];
    $ubicacion_lat = $_POST['ubicacion_lat'];
    $ubicacion_long = $_POST['ubicacion_long'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar contraseña
    $tipo_usuario = $_POST['tipo_usuario'];


    // Insertar usuario en la base de datos
    $sql = "INSERT INTO Usuarios (nombre, apellidos, telefono, email, genero, fecha_nac, ubicacion_lat, ubicacion_long, Contrasena, tipo_usuario)
            VALUES ('$nombre', '$apellidos', '$telefono', '$email', '$genero', '$fecha_nac', '$ubicacion_lat', '$ubicacion_long', '$contrasena', '$tipo_usuario')";

    if ($mysqli->query($sql) === TRUE) {
        $usuario_id = $mysqli->insert_id; // Obtener el ID del usuario insertado

        if ($tipo_usuario == 'dueño') {
            $nombre_establecimiento = $_POST['nombre_establecimiento'];
            $direccion_establecimiento = $_POST['direccion_establecimiento'];

            // Obtener coordenadas del establecimiento desde el caché o la API de Nominatim
            $coordenadas = obtenerCoordenadas($direccion_establecimiento);

            if ($coordenadas) {
                $ubicacion_lat_establecimiento = $coordenadas['lat'];
                $ubicacion_long_establecimiento = $coordenadas['lon'];

                $sql_discoteca = "INSERT INTO Discotecas (Nombre, Direccion, Ubicacion_lat, Ubicacion_long, id_usuario)
                                  VALUES ('$nombre_establecimiento', '$direccion_establecimiento', '$ubicacion_lat_establecimiento', '$ubicacion_long_establecimiento', '$usuario_id')";

                if ($mysqli->query($sql_discoteca) === TRUE) {
                    echo "Discoteca registrada exitosamente";
                } else {
                    echo "Error al registrar la discoteca: " . $mysqli->error;
                }
            } else {
                echo "Error al obtener las coordenadas de la dirección";
            }
        }

        // Redirigir después de completar el registro
        header("Location: ../index.php"); 
        exit(); 
    } else {
        echo "Error al registrar el usuario: " . $sql . "<br>" . $mysqli->error;
    }

    // Cerrar conexión
    $mysqli->close();
}

// Función para obtener las coordenadas desde el caché o la API de Nominatim
function obtenerCoordenadas($direccion) {
    $cacheFile = './cache/coordenadas.json'; // Ruta al archivo de caché

    if (!file_exists($cacheFile)) {
       
        file_put_contents($cacheFile, json_encode([]));
    }

    $cacheData = file_get_contents($cacheFile);
    $cache = json_decode($cacheData, true);

    if (isset($cache[$direccion])) {
        return $cache[$direccion];
    }

    // Si la dirección no está en el caché, obtener las coordenadas de Nominatim
    $direccionEncoded = urlencode($direccion);
    $geocodeUrl = "https://nominatim.openstreetmap.org/search?q=$direccionEncoded&format=json&limit=1";

    $options = array(
        'http' => array(
            'header' => "User-Agent: MyPHPScript/1.0\r\n"
        )
    );

    $context = stream_context_create($options);

    $geocodeResponse = file_get_contents($geocodeUrl, false, $context);

    if ($geocodeResponse === false) {
        return null;
    }

    $geocodeData = json_decode($geocodeResponse, true);

    if (!empty($geocodeData)) {
        $coordinates = [
            'lat' => $geocodeData[0]['lat'],
            'lon' => $geocodeData[0]['lon']
        ];

        // Guardar en caché la respuesta para futuras peticiones
        $cache[$direccion] = $coordinates;
        file_put_contents($cacheFile, json_encode($cache));

        return $coordinates;
    } else {
        return null;
    }
}
?>
