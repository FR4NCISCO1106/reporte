<?php
session_start(); 
include("conexion.php"); 

if (!isset($_SESSION['empresa_id'])) {
    header("Location: reporte/login"); 
    exit(); 
}

$empresa_id = $_SESSION['empresa_id'];

// 1. Captura de datos (asegurando que coincidan con registro_produccion.php)
$producto_id         = mysqli_real_escape_string($conexion, $_POST['producto_id']);
$nombre_presentacion = mysqli_real_escape_string($conexion, $_POST['nombre_presentacion']);
$nombre_medida       = mysqli_real_escape_string($conexion, $_POST['nombre_medida']);
$cantidad_unidades   = $_POST['cantidad_unidades'] ?? 0;
$unidades_activas    = $_POST['unidades_activas'] ?? 0;
$unidades_inactivas  = $_POST['unidades_inactivas'] ?? 0;
$fecha_produccion    = $_POST['fecha_produccion'] ?? date('Y-m-d');
$observaciones       = mysqli_real_escape_string($conexion, $_POST['observaciones']);

// 2. Insertar directamente (usando los nuevos campos de texto)
$sql = "INSERT INTO produccion (
            fecha_produccion, 
            producto_id, 
            empresa_id, 
            nombre_presentacion, 
            nombre_medida, 
            cantidad_unidades, 
            unidades_activas,
            unidades_inactivas,
            observaciones
        ) VALUES (
            '$fecha_produccion', 
            '$producto_id', 
            '$empresa_id', 
            '$nombre_presentacion', 
            '$nombre_medida', 
            '$cantidad_unidades', 
            '$unidades_activas',
            '$unidades_inactivas',
            '$observaciones'
        )";

if (mysqli_query($conexion, $sql)) {
    echo "<script>
            alert('¡Producción guardada con éxito!'); 
            window.location='registro_produccion.php';
          </script>";
} else {
    // Si falla, esto nos dirá exactamente por qué
    die("Error al registrar: " . mysqli_error($conexion));
}
?>