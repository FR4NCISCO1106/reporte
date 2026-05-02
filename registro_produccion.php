<?php
session_start(); 
if (!isset($_SESSION['empresa_id'])) {
    header("Location: reporte/login"); 
    exit(); 
}
include("conexion.php"); 

$empresa_id = $_SESSION['empresa_id'];
$empresa_nombre = $_SESSION['empresa_nombre'];

include("includes/header.php");
include("includes/sidebar.php");

// Consultas para los filtros predictivos (Datalists)
$productos_query = mysqli_query($conexion, "SELECT DISTINCT nombre_producto FROM productos WHERE empresa_id = $empresa_id");
// Se agregaron estas consultas siguiendo la estructura de registro_produccion_3.php
$presentaciones_query = @mysqli_query($conexion, "SELECT DISTINCT nombre_presentacion FROM presentaciones WHERE empresa_id = $empresa_id");
$medidas_query = @mysqli_query($conexion, "SELECT DISTINCT nombre_medida FROM unidades_medida");
?>

<style>
    :root { 
        --skill-bg: #f4f4f7; 
        --skill-card: #ffffff; 
        --skill-dark: #1a1a1a; 
        --skill-blue: #3366ff;
    }
    #layoutSidenav_content { background-color: var(--skill-bg); min-height: 100vh; }
    
    .skill-card {
        background: var(--skill-card);
        border-radius: 30px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.03);
        padding: 40px;
        margin-bottom: 50px;
    }

    .form-label {
        font-weight: 700;
        font-size: 0.72rem;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
        display: block;
    }

    #layoutSidenav_content .skill-card .form-control {
        border-radius: 50px !important;
        background-color: #f0f1f44e !important; /* Gris un poco más oscuro para que contraste */
        border: 2px solid #1b1b1b3b !important; /* El color de borde estándar de SB Admin pero forzado */
        padding: 14px 22px !important;
        height: auto !important; /* Asegura que el padding no se ignore */
        color: #495057 !important;
    }

    /* Efecto al escribir */
    #layoutSidenav_content .skill-card .form-control:focus {
        background-color: #ffffff !important;
        border-color: #565656 !important; /* Usamos tu azul variable */
        box-shadow: 0 0 0 0.2rem rgba(20, 34, 78, 0.25) !important;
    }
    
    .btn-save {
        background-color: var(--skill-dark);
        color: white;
        border-radius: 50px;
        padding: 16px 45px;
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .section-header { 
        margin-bottom: 25px; 
        border-bottom: 1px solid #f0f0f0; 
        padding-bottom: 15px; 
    }
    
    .section-title { 
        font-weight: 800; 
        color: var(--skill-dark); 
        font-size: 0.85rem; 
        text-transform: uppercase;
    }
    
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-5">
            <div class="mt-5 mb-5">
                <h2 class="fw-bold mb-1" style="letter-spacing: -1.5px; font-size: 2.2rem;">Cargar Producción</h2>
                <p class="text-muted">Empresa: <strong><?php echo $empresa_nombre; ?></strong></p>
            </div>

            <div class="skill-card">
                <form action="guardar_produccion.php" method="POST">
                    
                    <!-- SECCIÓN: DETALLE DE PRODUCTO -->
                    <div class="section-header">
                        <span class="section-title">Detalle del Producto</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Producto</label>
                            <input list="lista-productos" name="nombre_producto" class="form-control" placeholder="Buscar..." required autocomplete="off">
                            <datalist id="lista-productos">
                                <?php if($productos_query): while($p = mysqli_fetch_assoc($productos_query)): ?>
                                    <option value="<?php echo htmlspecialchars($p['nombre_producto']); ?>">
                                <?php endwhile; endif; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Presentación</label>
                            <input list="lista-presentaciones" name="nombre_presentacion" class="form-control" placeholder="Ej: Saco, Botella..." required autocomplete="off">
                            <datalist id="lista-presentaciones">
                                <?php if($presentaciones_query): while($pr = mysqli_fetch_assoc($presentaciones_query)): ?>
                                    <option value="<?php echo htmlspecialchars($pr['nombre_presentacion']); ?>">
                                <?php endwhile; endif; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Unidad de Medida</label>
                            <input list="lista-medidas" name="nombre_medida" class="form-control" placeholder="Ej: KG, LT..." required autocomplete="off">
                            <datalist id="lista-medidas">
                                <?php if($medidas_query): while($m = mysqli_fetch_assoc($medidas_query)): ?>
                                    <option value="<?php echo htmlspecialchars($m['nombre_medida']); ?>">
                                <?php endwhile; endif; ?>
                            </datalist>
                        </div>
                    </div>

                    <!-- SECCIÓN: CANTIDADES -->
                    <div class="section-header">
                        <span class="section-title">Cantidades y Fecha</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Cantidad Unidades</label>
                            <input type="number" name="cantidad_unidades" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Toneladas Métricas</label>
                            <input type="number" step="0.0001" name="toneladas_producidas" class="form-control" placeholder="0.0000" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Fecha de Producción</label>
                            <input type="date" name="fecha_produccion" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas sobre el lote de producción..."></textarea>
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