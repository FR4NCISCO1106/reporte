<?php
session_start(); 

// 1. Verificación de Seguridad
if (!isset($_SESSION['empresa_id'])) {
    header("Location: reporte/login"); 
    exit(); 
}

include("conexion.php"); 

// 2. Captura de datos de la sesión y el formulario
$empresa_id = $_SESSION['empresa_id'];

$nombre_producto     = mysqli_real_escape_string($conexion, $_POST['nombre_producto']);
$nombre_presentacion = mysqli_real_escape_string($conexion, $_POST['nombre_presentacion']);
$nombre_medida       = mysqli_real_escape_string($conexion, $_POST['nombre_medida']);
$cantidad_unidades   = $_POST['cantidad_unidades'] ?? 0;
$toneladas           = $_POST['toneladas_producidas'] ?? 0;
$fecha_produccion    = $_POST['fecha_produccion'] ?? date('Y-m-d');
$observaciones       = mysqli_real_escape_string($conexion, $_POST['observaciones']);

// 3. Validar que el Producto existe para ESTA empresa
$query_prod = "SELECT id FROM productos WHERE nombre_producto = '$nombre_producto' AND empresa_id = '$empresa_id' LIMIT 1";
$res_prod = mysqli_query($conexion, $query_prod);
$data_prod = mysqli_fetch_assoc($res_prod);

if (!$data_prod) {
    echo "<script>alert('Error: El producto no existe en tu catálogo.'); window.history.back();</script>";
    exit();
}
$producto_id = $data_prod['id'];

// 4. Obtener ID de Presentación (opcional: podrías validarlo igual que el producto)
$query_pres = "SELECT id FROM presentaciones WHERE nombre_presentacion = '$nombre_presentacion' AND empresa_id = '$empresa_id' LIMIT 1";
$res_pres = mysqli_query($conexion, $query_pres);
$data_pres = mysqli_fetch_assoc($res_pres);
$presentacion_id = $data_pres['id'] ?? 'NULL';

// 5. Obtener ID de Unidad de Medida
$query_med = "SELECT id FROM unidades_medida WHERE nombre_medida = '$nombre_medida' LIMIT 1";
$res_med = mysqli_query($conexion, $query_med);
$data_med = mysqli_fetch_assoc($res_med);
$medida_id = $data_med['id'] ?? 'NULL';

// 6. Insertar en la tabla de producción
// Asegúrate de que tu tabla 'produccion' tenga las columnas: presentacion_id y unidad_medida_id
$sql = "INSERT INTO produccion (
            fecha_produccion, 
            producto_id, 
            empresa_id, 
            presentacion_id, 
            unidad_medida_id, 
            cantidad_unidades, 
            toneladas_producidas, 
            observaciones
        ) VALUES (
            '$fecha_produccion', 
            '$producto_id', 
            '$empresa_id', 
            $presentacion_id, 
            $medida_id, 
            '$cantidad_unidades', 
            '$toneladas', 
            '$observaciones'
        )";

if (mysqli_query($conexion, $sql)) {
    echo "<script>
            alert('¡Producción guardada con éxito!'); 
            window.location='registro_produccion.php';
          </script>";
} else {
    echo "Error técnico al guardar: " . mysqli_error($conexion);
}
?>