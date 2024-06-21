<?php
session_start();

if (isset($_SESSION['eventos_usuario']) && !empty($_SESSION['eventos_usuario'])) {
    foreach ($_SESSION['eventos_usuario'] as $evento) {
        if (isset($evento['Id_evento']) && isset($evento['Nombre_discoteca']) && isset($evento['Nombre']) && isset($evento['Fecha'])) {
            $fechaEvento = strtotime($evento['Fecha']);
            $fechaActual = strtotime(date('Y-m-d')); // Fecha actual sin hora

            if ($fechaEvento >= $fechaActual) {
                echo "<div class='evento-seleccionado'>";
                echo "<input type='checkbox' name='eventos[]' value='{$usuario['Id_evento']}'> <strong>{$usuario['Nombre_discoteca']}</strong>: {$evento['Nombre']} {$evento['Fecha']}";
                echo "</div>";
            }
        } else {
            echo "Datos del evento incompletos.";
        }
    }
} else {
    echo "No hay eventos disponibles.";
}
?>
