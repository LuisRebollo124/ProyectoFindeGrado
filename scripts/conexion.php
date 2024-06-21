<?php
// Variables de la conexion a la DB
$mysqli = new mysqli("localhost", "root", "KasperLR124", "AppMovil_Discotecas");

// Comprobamos la conexion
if ($mysqli->connect_errno) {
    die("Fallo la conexion: " . $mysqli->connect_error);
}
?>
