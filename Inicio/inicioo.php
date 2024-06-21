<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to bottom, #b19cd9, #d8bfd8); 
            margin: 0;
        }

        form {
            background-color: #e6e6fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            max-height: 400px; /* Set a maximum height for the form */
            overflow-y: auto; /* Add vertical scroll if content overflows */
        }

        h2 {
            text-align: center;
            color: #4b0082;
            font-family: Arial, sans-serif;
        }

        label {
            display: block;
            margin-top: 10px;
            color: #4b0082;
            font-family: Arial, sans-serif;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
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

        #logo{
            width:300px;
            height:300px;
        }
    </style>
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocalización no es soportada por este navegador.");
            }
        }

        function showPosition(position) {
            document.getElementById('ubicacion_lat').value = position.coords.latitude;
            document.getElementById('ubicacion_long').value = position.coords.longitude;
        }

        function showError(error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert("Usuario denegó la solicitud de geolocalización.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Información de ubicación no está disponible.");
                    break;
                case error.TIMEOUT:
                    alert("La solicitud de ubicación ha caducado.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("Un error desconocido ocurrió.");
                    break;
            }
        }

        window.onload = getLocation;
    </script>
</head>
<body>
    <img src="../img/logo.png" id="logo">
    <form action="inicio.php" method="POST">
        <h2>Inicio de Sesión</h2>
        <?php  
        $errorusuario = "NO";

        if (isset($_GET["errorusuario"]) && $_GET["errorusuario"] == "SI") { 
            ?><h4>Lo siento, el usuario o la contraseña son incorrectas, por favor vuelva a introducirlas.</h4><?php
        } else {
            ?><h4>Por favor introduzca la contraseña y el email. </h4><?php
        }
        
        ?>
    
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required><br>

        <input type="hidden" id="ubicacion_lat" name="ubicacion_lat" required>
        <input type="hidden" id="ubicacion_long" name="ubicacion_long" required> <br>

        <a href="../Registro/register.html">No tengo cuenta</a>

        <button type="submit">Iniciar Sesión</button>
    </form>

</body>
</html>
