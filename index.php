<?php

    include ("scripts/seguridad.php");

    // Verifica si el usuario está autenticado
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        echo json_encode(array("error" => "Usuario no autenticado"));
        exit;
    }

    // Obtener datos del usuario desde la sesión
    $usuario_id = $_SESSION['usuario_id'];
    $nombre_usuario = $_SESSION['nombre'];

    // Conexión a la base de datos
    include ("scripts/conexion.php");

    // Consulta SQL para obtener nombre y apellidos del usuario
    $sql = "SELECT e.id_evento, e.Nombre as Nombre_evento, e.Fecha, d.Nombre as Nombre_discoteca, u.Nombre as Nombre_usuario, u.Apellidos, u.tipo_usuario
    FROM Eventos e
    RIGHT JOIN Discotecas d ON e.Id_discoteca = d.Id_discoteca
    RIGHT JOIN Asistencia a ON e.id_evento = a.id_evento
    RIGHT JOIN Usuarios u ON a.id_usuario = u.id_usuario
    WHERE a.id_usuario = ? AND e.Fecha >= CURDATE()";    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el usuario
    if ($result && $result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        $usuario = array("Nombre_usuario" => "Usuario", "Apellidos" => "Desconocido");
    }

    $stmt->close();
    $mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eventix</title>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <style>
            body, html {
                height: 100%;
                margin: 0;
                display: flex;
                flex-direction: column;
            }
            #header, #footer {
                background-color: #333;
                color: white;
                padding: 10px;
                text-align: center;
                background-color: #b19cd9;
                height: 60px;
                border-radius: 5px;
            }

            h2{
                color:black;
            }

            #header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            #map {
                flex: 1;
            }
            #search-bar {
                width: 50%;
                height: 20px;
            }
            .dropdown {
                display: none;
                position: absolute;
                background-color: white;
                border: 1px solid #ccc;
                z-index: 1000;
                padding: 10px;
                max-width: 300px;
            }
            #menuDropdown {
                top: 60px; /* Justo debajo del header */
                left: 10px; /* Alineado con el botón de menú */
                background-color: #e1daf0;
            }
            #profileDropdown {
                top: 60px; /* Justo debajo del header */
                right: 10px; /* Alineado con el botón de perfil */
                background-color: #e1daf0;
            }

            #profileButton {
                background-image: url('img/perfil.png');
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                width: 40px;  /* Ajusta el tamaño según sea necesario */
                height: 40px;
                border: none;
                padding: 0;
                margin: 0;
                cursor: pointer;
                background-color: transparent;
                outline: none;
            }

            #menuButton {
                background-image: url('img/logo.png');
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                width: 60px;  /* Ajusta el tamaño según sea necesario */
                height: 60px;
                border: none;
                padding: 0;
                margin: 0;
                cursor: pointer;
                background-color: transparent;
                outline: none;
            } 

            #discotecaDropdown {
                top: 60px; /* Posición inicial, se actualizará según el marcador */
                left: 50%; /* Centrado en la pantalla */
                transform: translateX(-50%);
                background-color: #e1daf0;
                position: fixed;
                border-radius: 10px;
            }
            .close-button {
                position: absolute;
                top: 5px;
                right: 5px;
                background: none;
                border: none;
                font-size: 16px;
                cursor: pointer;
            }

            #logo{
                width:70px;
                height:70px;
            }   

            #misEventos {
                position: fixed;
                top: 200px;
                right: 20px;
                background-color: #b19cd9;
                border: 1px solid #ccc;
                padding: 20px;
                width: 300px; /* Ancho inicial */
                max-height: calc(100% - 100px); /* Altura máxima menos los márgenes superior e inferior */
                overflow-y: auto; /* Permitir desplazamiento vertical si es necesario */
                transition: width 0.3s ease; /* Transición para el crecimiento del ancho */
                z-index: 1100; /* Z-index superior al mapa */
                border-radius: 30px ;
            }

            #VerEventosButton {
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

            #VerEventosButton:hover {
                background-color: #7a50b9;
            }

            #addEventButton {
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

            #addEventButton:hover {
                background-color: #7a50b9;
            }


            #desapuntareventos {
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

            #desapuntareventos:hover {
                background-color: #7a50b9;
            }
        </style>
    </head>
    <body>
        <div id="header">
            <button id="menuButton"></button>
            <input type="text" id="search-bar" placeholder="Buscar discotecas...">
            <button id="profileButton"></button>
        </div>
        <div id="map" style="height: 100%;"></div>
        
        <div id="misEventos">
            <h1>Próximos Eventos</h1>
            <form action="Asistencia/desapuntarse_evento.php" id="eventForm">
                <?php
                // Mostrar eventos del usuario si existen en la sesión y su fecha es posterior a la actual
                if (isset($_SESSION['eventos_usuario'])) {
                    foreach ($_SESSION['eventos_usuario'] as $evento) {
                        $fechaEvento = strtotime($evento['Fecha']);
                        $fechaActual = strtotime(date('Y-m-d')); // Fecha actual sin hora

                        if ($fechaEvento >= $fechaActual) {
                            echo "<div class='evento-seleccionado'>";
                            echo "<input type='checkbox' name='eventos[]' value='{$usuario['id_evento']}'> <strong>{$usuario['Nombre_discoteca']}</strong>: {$evento['Nombre']} {$evento['Fecha']}";
                            echo "</div>";
                        }
                    }
                }
                ?>
                <br>
                <button type="submit" id="desapuntareventos">Desapuntarse de Eventos</button>
            </form>
        </div>


        <div id="footer"></div>

        <div id="menuDropdown" class="dropdown">Contenido del Menú</div>
        <div id="profileDropdown" class="dropdown">
            <h2>Bienvenid@, <?php echo $usuario['Nombre_usuario'] . ' ' . $usuario['Apellidos']; ?></h2> 
            <a href="#" id="logoutLink">Cerrar sesión</a>
        </div>
        <div id="discotecaDropdown" class="dropdown">
            <button class="close-button" onclick="closeDropdown('discotecaDropdown')">X</button>
            <h1 id="discotecaNombre"></h1>
            <p id="discotecaDireccion"></p>
            <button id="addEventButton" style="display: none;">Añadir Evento</button>
            <button id="VerEventosButton">Ver Eventos</button>
        </div>

        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>
            let map;
            const menuButton = document.getElementById('menuButton');
            const profileButton = document.getElementById('profileButton');
            const menuDropdown = document.getElementById('menuDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const discotecaDropdown = document.getElementById('discotecaDropdown');
            const searchBar = document.getElementById('search-bar');


            let currentMarker = null;
            let currentUser = {
                id: <?php echo $usuario_id; ?>, // ID del usuario autenticado
                ownedDiscotecas: [] // Lista de discotecas que el usuario es dueño (puedes poblar esta lista si tienes esa información)
            };
            let discotecas = [];
            let userLocation = null;

            function toggleDropdown(dropdown) {
                if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            }

            menuButton.addEventListener('click', () => {
                toggleDropdown(menuDropdown);
                profileDropdown.style.display = 'none';
                discotecaDropdown.style.display = 'none';
            });

            profileButton.addEventListener('click', () => {
                toggleDropdown(profileDropdown);
                menuDropdown.style.display = 'none';
                discotecaDropdown.style.display = 'none';
            });

            function closeDropdown(dropdownId) {
                document.getElementById(dropdownId).style.display = 'none';
                currentMarker = null;
            }

            // Cerrar sesión y redirigir al inicio de sesión
            document.getElementById('logoutLink').addEventListener('click', (event) => {
                event.preventDefault(); // Evitar que el enlace recargue la página
                window.location.href = 'scripts/cerrar_sesion.php';
            });

            function initMap() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((position) => {
                        userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };

                        map = L.map('map').setView([userLocation.lat, userLocation.lng], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);

                        L.marker([userLocation.lat, userLocation.lng]).addTo(map)
                            .bindPopup('Tu ubicación')
                            .openPopup();

                        // Añadir evento de movimiento del mapa después de la inicialización
                        map.on('move', updateDiscotecaDropdownPosition);

                        // Cargar y mostrar discotecas
                        fetch('Discotecas/discotecas.php')
                            .then(response => response.json())
                            .then(data => {
                                console.log('Datos recibidos:', data); // Log para verificar los datos recibidos

                                discotecas = data; // Guardar discotecas en una variable global
                                discotecas.forEach(discoteca => {
                                    console.log('Datos de la discoteca:', discoteca); // Log para verificar los datos de la discoteca

                                    const marker = L.marker([discoteca.Ubicacion_lat, discoteca.Ubicacion_long], {
                                        discotecaId: discoteca.id_usuario // Cambiado a id_usuario
                                    }).addTo(map);

                                    marker.on('click', () => {
                                        currentMarker = marker;
                                        toggleDropdown(discotecaDropdown);
                                        menuDropdown.style.display = 'none';
                                        profileDropdown.style.display = 'none';
                                        document.getElementById('discotecaNombre').innerText = discoteca.Nombre;
                                        document.getElementById('discotecaDireccion').innerText = discoteca.Direccion;
                                        //document.getElementById('verEventosLink').innerText = discoteca.Direccion;
                                        console.log('ID de la discoteca:', currentMarker.options.discotecaId); // Agregar log para depuración
                                        updateDiscotecaDropdownPosition();
                                    });

                                    // Añadir discoteca a la lista de discotecas propias si el usuario es dueño
                                    if (discoteca.id_usuario == currentUser.id) {
                                        currentUser.ownedDiscotecas.push(discoteca);
                                    }
                                });
                            });
                    });
                } else {
                    alert('Geolocation is not supported by this browser.');
                }
            }

            initMap();

            function updateDiscotecaDropdownPosition() {
                if (currentMarker) {
                    const { lat, lng } = currentMarker.getLatLng();
                    const point = map.latLngToContainerPoint([lat, lng]);
                    discotecaDropdown.style.top = `${point.y + 15}px`; // Ajuste para que esté un poco más abajo del marcador
                    discotecaDropdown.style.left = `${point.x}px`;

                    const discotecaId = currentMarker.options.discotecaId;
                    console.log('updateDiscotecaDropdownPosition called');
                    console.log('ID de la discoteca en updateDiscotecaDropdownPosition:', discotecaId);

                    // Mostrar el botón "Añadir Evento" solo si el usuario es dueño de la discoteca actual
                    const isOwner = currentUser.id == discotecaId; // Comparar el id del usuario actual con el id de la discoteca
                    const addEventButton = document.getElementById('addEventButton');
                    if (isOwner) {
                        addEventButton.style.display = 'block';
                    } else {
                        addEventButton.style.display = 'none';
                    }
                }
            }

            // Buscador de discotecas
            searchBar.addEventListener('input', function() {
                const searchTerm = searchBar.value.toLowerCase();
                if (searchTerm === '') {
                    if (userLocation) {
                        map.setView([userLocation.lat, userLocation.lng], 15);
                    }
                } else {
                    const discoteca = discotecas.find(d => d.Nombre.toLowerCase().includes(searchTerm));
                    if (discoteca) {
                        map.setView([discoteca.Ubicacion_lat, discoteca.Ubicacion_long], 18);
                    }
                }
            });

            // Añadir evento a una discoteca
            document.getElementById('addEventButton').addEventListener('click', () => {
                if (currentMarker) {
                    const discotecaId = currentMarker.options.discotecaId;
                    window.location.href = `eventos/eventos.html?id_discoteca=${discotecaId}`;
                }
            });

            document.getElementById('VerEventosButton').addEventListener('click', () => {
                if (currentMarker) {
                    const discotecaId = currentMarker.options.discotecaId;
                    window.location.href = `eventos/ver_eventos.php?id_discoteca=${discotecaId}`;
                }
            });

           // JavaScript en index.php

        
           document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('eventForm');
                const button = document.getElementById('desapuntareventos');
                const misEventosDiv = document.getElementById('misEventos');

                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Evitar el envío tradicional del formulario

                    const formData = new FormData(form);
                    const eventosSeleccionados = formData.getAll('eventos[]');

                    if (eventosSeleccionados.length > 0) {
                        // Construir el objeto de datos a enviar
                        const data = {
                            eventos_seleccionados: eventosSeleccionados
                        };

                        // Enviar la solicitud POST usando fetch
                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                // Recargar eventos después de la eliminación exitosa
                                recargarEventos();
                            } else {
                                console.error(result.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                    } else {
                        console.log("Error: No se han seleccionado eventos para desapuntar.");
                        // Manejar el caso en el que no se hayan seleccionado eventos
                    }
                });

                function recargarEventos() {
                    fetch('recargar.php')
                    .then(response => response.text())
                    .then(data => {
                        misEventosDiv.innerHTML = `<h1>Próximos Eventos</h1>${data}<br><button type="submit" id="desapuntareventos">Desapuntarse de Eventos</button>`;
                    })
                    .catch(error => {
                        console.error('Error al recargar eventos:', error);
                    });
                }
            });



        </script>

    </body>
</html>

