<?php
session_start(); 
if (!isset($_SESSION['empresa_id'])) {
    header("Location: reporte/login"); 
    exit(); 
}
include("conexion.php"); 

$empresa_id = $_SESSION['empresa_id'];
$empresa_nombre = $_SESSION['empresa_nombre'];

$catalogos = [
    'Argelia Laya' => [
        'presentaciones' => ['Unidad', 'Envase plástico'],
        'medidas' => ['350g']
    ],
    'Diana' => [
        'presentaciones' => ['Bolsa plástica', 'Envase plástico'],
        'medidas' => ['760 ml', '250 g', '500g', '5Kg']
    ],
    'Inn' => [
        'presentaciones' => ['Bolsa', 'Unidad'],
        'medidas' => ['250g', '500g', '1Kg']
    ],
    'La Fina' => [
        'presentaciones' => ['Caja', 'Cuñete', 'Envase plástico', 'Frasco de vidrio', 'Plastico'],
        'medidas' => ['230g', '5Kg', '1Lts', '18Lts']
    ],
    'Los Andes' => [
        'presentaciones' => ['Bolsa', 'Chocolate liquido', 'Envase', 'Envase plástico', 'Foil', 'Frasco de vidrio', 'Hojalata', 'Lata', 'Leche achocolatada', 'Pote', 'Yogurt liquido', 'Yogurt con cereal', 'Yogurt firme'],
        'medidas' => ['125g', '150g', '200g', '250g', '270g', '350g', '370g', '390g', '397g', '398g', '400g', '500g', '900g', '200ml', '250ml', '400ml', '900ml', '1Lts', '1.8Lts']
    ],
    'Sabilven' => [
        'presentaciones' => ['Empaque de polipropileno', 'Tambor', 'Unidad'],
        'medidas' => ['330cc', '10g', '20g', '100g', '200g', '500g', '200kg']
    ]
];

$opciones = $catalogos[$empresa_nombre] ?? ['presentaciones' => [], 'medidas' => []];

include("includes/header.php");
include("includes/sidebar.php");

// Consultamos productos. Asegúrate que en la BD existan productos con empresa_id = $empresa_id
$productos_query = mysqli_query($conexion, "SELECT id, nombre_producto FROM productos WHERE empresa_id = '$empresa_id'");
?>

<style>
    :root { 
        --skill-bg: #f4f4f7; 
        --skill-card: #ffffff; 
        --skill-dark: #1a1a1a; 
    }
    /* Ajuste para eliminar el espacio blanco lateral */
    #layoutSidenav_content { 
        background-color: var(--skill-bg); 
        min-height: 100vh; 
        width: 100%;
        margin-left: 0; 
    }
    .skill-card { background: var(--skill-card); border-radius: 30px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.03); padding: 40px; margin-bottom: 50px; }
    .form-label { font-weight: 700; font-size: 0.72rem; color: #000000; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 10px; display: block; }
    #layoutSidenav_content .skill-card .form-control { border-radius: 50px !important; background-color: #f0f1f44e !important; border: 2px solid #1b1b1b3b !important; padding: 14px 22px !important; height: auto !important; color: #495057 !important; }
    .btn-save { background-color: var(--skill-dark); color: white; border-radius: 50px; padding: 16px 45px; font-weight: 700; border: none; }
    .section-header { margin-bottom: 25px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
    .section-title { font-weight: 800; color: var(--skill-dark); font-size: 0.85rem; text-transform: uppercase; }
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-md-5"> <div class="py-5">
                <h2 class="fw-bold mb-1" style="letter-spacing: -1.5px; font-size: 2.2rem;">Cargar Producción</h2>
                <p class="text-muted">Empresa: <strong><?php echo $empresa_nombre; ?></strong></p>
            </div>

            <div class="skill-card">
                <form action="guardar_produccion.php" method="POST">
                    
                    <div class="section-header">
                        <span class="section-title">Detalle del Producto</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Producto</label>
                            <select name="producto_id" class="form-control" required>
                                <option value="" disabled selected>Seleccione producto</option>
                                <?php 
                                if (mysqli_num_rows($productos_query) > 0) {
                                    while($p = mysqli_fetch_assoc($productos_query)) {
                                        echo '<option value="'.$p['id'].'">'.htmlspecialchars($p['nombre_producto']).'</option>';
                                    }
                                } else {
                                    echo '<option value="" disabled>No hay productos registrados para esta empresa (ID: '.$empresa_id.')</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Presentación</label>
                            <select name="nombre_presentacion" class="form-control" required>
                                <option value="" disabled selected>Seleccione presentación</option>
                                <?php foreach($opciones['presentaciones'] as $pres): ?>
                                    <option value="<?php echo $pres; ?>"><?php echo $pres; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Medida de Unidad</label>
                            <select name="nombre_medida" class="form-control" required>
                                <option value="" disabled selected>Seleccione medida</option>
                                <?php foreach($opciones['medidas'] as $med): ?>
                                    <option value="<?php echo $med; ?>"><?php echo $med; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-header">
                        <span class="section-title">Cantidades y Estado</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3 mb-4">
                            <label class="form-label">Unidades Activas</label>
                            <input type="number" name="unidades_activas" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="form-label">Unidades Inactivas</label>
                            <input type="number" name="unidades_inactivas" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="form-label">Cantidad De Unidades</label>
                            <input type="number" name="cantidad_unidades" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="form-label">Fecha de Producción</label>
                            <input type="date" name="fecha_produccion" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas sobre el lote..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-check-circle me-2"></i> Finalizar y Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>