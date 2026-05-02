<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['empresa_id'])) {
    exit("Acceso denegado");
}

$empresa_id = $_SESSION['empresa_id'];
$nombre_producto = mysqli_real_escape_string($conexion, $_POST['nombre_producto']);
$codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);

// Verificamos si ya existe para esta misma empresa
$check = mysqli_query($conexion, "SELECT id FROM productos WHERE nombre_producto = '$nombre_producto' AND empresa_id = '$empresa_id'");

if (mysqli_num_rows($check) > 0) {
    echo "<script>alert('Este producto ya existe en tu catálogo'); window.history.back();</script>";
} else {
    $sql = "INSERT INTO productos (nombre_producto, empresa_id, codigo) VALUES ('$nombre_producto', '$empresa_id', '$codigo')";
    if (mysqli_query($conexion, $sql)) {
        header("Location: reporte/agregar_producto");
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>