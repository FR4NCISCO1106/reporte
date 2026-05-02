<?php
session_start(); 

if (!isset($_SESSION['empresa_id'])) {
    header("Location: ./login"); 
    exit(); 
}

include("conexion.php"); 

// Empresa de la sesión
$id_sesion = $_SESSION['empresa_id'];
$nombre_sesion = $_SESSION['empresa_nombre'];

// Capturar filtros
$filtro_empresa = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : null;
$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Condición de fecha para las consultas[cite: 2]
$condicion_fecha = "";
if ($fecha_inicio && $fecha_fin) {
    $condicion_fecha = " AND p.fecha_produccion BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

// --- LÓGICA DE CONTADORES (KPIs) ---
$tm_query = mysqli_query($conexion, "SELECT SUM(toneladas_producidas) as total FROM produccion p WHERE empresa_id = $id_sesion $condicion_fecha");
$tm_data = mysqli_fetch_assoc($tm_query);
$total_tm = $tm_data['total'] ?? 0;

$unidades_query = mysqli_query($conexion, "SELECT SUM(cantidad_unidades) as total FROM produccion p WHERE empresa_id = $id_sesion $condicion_fecha");
$unidades_data = mysqli_fetch_assoc($unidades_query);
$total_unidades = $unidades_data['total'] ?? 0;

$variedad_query = mysqli_query($conexion, "SELECT COUNT(id) as total FROM productos WHERE empresa_id = $id_sesion");
$variedad_data = mysqli_fetch_assoc($variedad_query);
$total_variedad = $variedad_data['total'] ?? 0;

$total_empresas_global = mysqli_num_rows(mysqli_query($conexion, "SELECT id FROM empresas"));

// --- LÓGICA DEL GRÁFICO (Muestra todas las empresas) ---
$nombres_graf = [];
$totales_graf = [];

$sql_graf = "SELECT e.nombre, SUM(p.toneladas_producidas) as total 
              FROM empresas e 
              LEFT JOIN produccion p ON e.id = p.empresa_id $condicion_fecha 
              " . ($filtro_empresa ? "WHERE e.id = $filtro_empresa" : "") . " 
              GROUP BY e.id";

$graf_res = mysqli_query($conexion, $sql_graf);
while($g = mysqli_fetch_assoc($graf_res)){
    $nombres_graf[] = $g['nombre'];
    $totales_graf[] = $g['total'] ?? 0;
}

include("includes/header.php"); 
include("includes/sidebar.php"); 
?>

<!-- LIBRERÍAS DE CALENDARIO MODERNO[cite: 2] -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

<style>
    :root { 
        --skillset-bg: #f3f3f3; 
        --skillset-card-radius: 24px;
        --skillset-dark: #1a1a1a;
        --skillset-blue: #3366ff;
    }
    #layoutSidenav_content { background-color: var(--skillset-bg); }
    .card { border-radius: var(--skillset-card-radius) !important; border: none !important; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 1.5rem; min-height: 140px; display: flex; flex-direction: column; justify-content: center; }
    .kpi-dark { background-color: var(--skillset-dark); color: white; }
    .kpi-value { font-size: 1.8rem; font-weight: 700; margin-top: 5px; letter-spacing: -1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kpi-label { color: #8e8e93; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-pill { border-radius: 30px !important; padding: 10px 20px; font-weight: 600; background: white; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
    .btn-pill:hover { background: #eee; }
    .flatpickr-input { background-color: #f8f9fa !important; border: 1px solid #eee !important; padding: 12px 20px !important; border-radius: 15px !important; }

    .flatpickr-input { 
        background-color: #f8f9fa !important; 
        border: 1px solid #dee2e6 !important; 
        padding: 12px 20px !important; 
        border-radius: 15px !important; 
        color: #1a1a1a !important; 
        font-weight: 600 !important;
    }

    .flatpickr-input::placeholder {
        color: #6c757d !important; 
        opacity: 1; 
    }
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-5">
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mt-5 mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -1.5px;">REPORTE DE PRODUCCIÓN SEMANAL</h2>
                    <p class="text-muted small mb-0">Bienvenido, <strong><?php echo $nombre_sesion; ?></strong></p>
                </div>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-pill text-dark" data-bs-toggle="modal" data-bs-target="#modalFecha">
                        <i class="fas fa-calendar me-2 text-primary"></i> 
                        <?php echo $fecha_inicio ? "$fecha_inicio / $fecha_fin" : "Filtrar Periodo"; ?>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-pill text-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-industry me-2 text-warning"></i> 
                            <?php echo $filtro_empresa ? "Empresa Seleccionada" : "Todas las Empresas"; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius: 15px;">
                            <li><a class="dropdown-item" href="index.php">Todas las Empresas</a></li>
                            <?php
                            $el = mysqli_query($conexion, "SELECT * FROM empresas");
                            while($e = mysqli_fetch_assoc($el)) {
                                echo "<li><a class='dropdown-item' href='index.php?empresa_id={$e['id']}'>{$e['nombre']}</a></li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card kpi-dark">
                        <div class="kpi-label">Total TM. Producidas</div>
                        <div class="kpi-value"><?php echo number_format($total_tm, 2, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white">
                        <div class="kpi-label">Unidades Producidas</div>
                        <div class="kpi-value"><?php echo number_format($total_unidades, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white">
                        <div class="kpi-label">Variedad Productos</div>
                        <div class="kpi-value"><?php echo $total_variedad; ?></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white">
                        <div class="kpi-label">Empresas</div>
                        <div class="kpi-value"><?php echo $total_empresas_global; ?></div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICO -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4" style="color: var(--skillset-blue);">Producción por Empresa (TM)</h6>
                        <div style="height: 350px; position: relative;">
                            <canvas id="graficoProduccion"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA -->
            <div class="card mb-5 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small fw-bold">
                                    <th class="ps-4 py-3">FECHA</th>
                                    <th class="py-3">EMPRESA</th>
                                    <th class="py-3">PRODUCTO</th>
                                    <th class="py-3">UNIDADES</th>
                                    <th class="py-3">TOTAL TM</th>
                                    <th class="text-end pe-4 py-3">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $id_para_tabla = $filtro_empresa ? $filtro_empresa : $id_sesion;
                                $query_t = mysqli_query($conexion, "SELECT p.*, e.nombre as empresa, pr.nombre_producto 
                                    FROM produccion p 
                                    INNER JOIN empresas e ON p.empresa_id = e.id 
                                    INNER JOIN productos pr ON p.producto_id = pr.id 
                                    WHERE p.empresa_id = $id_para_tabla $condicion_fecha 
                                    ORDER BY p.fecha_produccion DESC LIMIT 10");
                                while($row = mysqli_fetch_assoc($query_t)): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small"><?php echo date('d M, Y', strtotime($row['fecha_produccion'])); ?></td>
                                        <td class="fw-bold"><?php echo $row['empresa']; ?></td>
                                        <td><?php echo $row['nombre_producto']; ?></td>
                                        <td><?php echo number_format($row['cantidad_unidades'], 0, ',', '.'); ?></td>
                                        <td class="fw-bold text-primary"><?php echo number_format($row['toneladas_producidas'], 3, ',', '.'); ?> TM</td>
                                        <td class="text-end pe-4"><span class="badge bg-light text-dark border-0 py-2 px-3" style="border-radius: 10px;">Procesado</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER CORREGIDO[cite: 1] -->
    <?php include("includes/footer.php"); ?>
</div>

<!-- MODAL DE FECHAS MEJORADO[cite: 2] -->
<div class="modal fade" id="modalFecha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
      <form action="index.php" method="GET">
        <div class="modal-body p-4">
            <h5 class="fw-bold mb-4">Filtrar por Periodo</h5>
            <div class="row mb-4">
                <div class="col-6">
                    <label class="small fw-bold text-muted mb-2 d-block">Desde</label>
                    <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control" placeholder="Seleccionar" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold text-muted mb-2 d-block">Hasta</label>
                    <input type="text" name="fecha_fin" id="fecha_fin" class="form-control" placeholder="Seleccionar" required>
                </div>
            </div>
            <!-- BOTONES AL LADO[cite: 2] -->
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-light w-50 rounded-pill py-2 fw-bold text-dark text-decoration-none text-center">Limpiar</a>
                <button type="submit" class="btn btn-dark w-50 rounded-pill py-2 fw-bold">Filtrar</button>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
    // Configuración Flatpickr en español[cite: 2]
    flatpickr("#fecha_inicio", { locale: "es", dateFormat: "Y-m-d" });
    flatpickr("#fecha_fin", { locale: "es", dateFormat: "Y-m-d" });

    const ctx = document.getElementById('graficoProduccion');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($nombres_graf); ?>,
            datasets: [{ 
                label: 'Toneladas', 
                data: <?php echo json_encode($totales_graf); ?>, 
                backgroundColor: '#1a1a1a', 
                borderRadius: 12, 
                barThickness: <?php echo (count($nombres_graf) > 1) ? 50 : 100; ?> 
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            scales: { 
                y: { beginAtZero: true, grid: { borderDash: [5, 5], drawBorder: false } }, 
                x: { grid: { display: false } } 
            }, 
            plugins: { legend: { display: false } } 
        }
    });
</script>