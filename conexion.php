<?php

// Configuración de la base de datos
$host = "localhost";    // El servidor suele ser localhost
$user = "root";         // Usuario por defecto en XAMPP es root
$pass = "";             // Por defecto la contraseña está vacía en XAMPP
$db   = "reporte";      // El nombre de la base de datos que creamos

// Crear la conexión
$conexion = mysqli_connect($host, $user, $pass, $db);

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a UTF-8 para evitar problemas con tildes y ñ
mysqli_set_charset($conexion, "utf8");
?>