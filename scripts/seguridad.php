<?php
session_start();
if ($_SESSION["autentificado"] != "SI") {
    header("Location: ../inicio/inicioo.php");
    exit();
}

?>
