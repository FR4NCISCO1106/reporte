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

// --- CONFIGURACIÓN DE PAGINACIÓN ---
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$inicio_limit = ($pagina_actual - 1) * $registros_por_pagina;

// Capturar filtros
$filtro_empresa = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : null;
$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Condición de fecha para las consultas
$condicion_fecha = "";
if ($fecha_inicio && $fecha_fin) {
    $condicion_fecha = " AND p.fecha_produccion BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

// Condición de filtro de empresa para los KPIs y Tabla
$id_filtro_kpi = $filtro_empresa ?: null;
$condicion_kpi = $id_filtro_kpi ? "WHERE p.empresa_id = $id_filtro_kpi" : "WHERE 1=1";

// --- FÓRMULA DE CONVERSIÓN DINÁMICA (CORREGIDA) ---
// Se agregó el REPLACE faltante para evitar el error de sintaxis en MariaDB
$formula_conversion = "CASE 
    WHEN p.nombre_medida LIKE '%kg' THEN (p.cantidad_unidades * CAST(REPLACE(REPLACE(p.nombre_medida, 'kg', ''), 'Kg', '') AS DECIMAL(10,2))) / 1000
    WHEN p.nombre_medida LIKE '%g'  THEN (p.cantidad_unidades * CAST(REPLACE(REPLACE(p.nombre_medida, 'g', ''), 'G', '') AS DECIMAL(10,2))) / 1000000
    WHEN p.nombre_medida LIKE '%lts' THEN (p.cantidad_unidades * CAST(REPLACE(REPLACE(p.nombre_medida, 'lts', ''), 'Lts', '') AS DECIMAL(10,2))) / 1000
    WHEN p.nombre_medida LIKE '%ml'  THEN (p.cantidad_unidades * CAST(REPLACE(REPLACE(p.nombre_medida, 'ml', ''), 'Ml', '') AS DECIMAL(10,2))) / 1000000
    ELSE p.toneladas_producidas 
END";

// --- CONSULTA PARA CONTAR TOTAL DE REGISTROS ---
$total_res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM produccion p $condicion_kpi $condicion_fecha");
$total_row = mysqli_fetch_assoc($total_res);
$total_registros = $total_row['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// --- LÓGICA DE CONTADORES (KPIs) ---
$tm_query = mysqli_query($conexion, "SELECT SUM($formula_conversion) as total FROM produccion p $condicion_kpi $condicion_fecha");
$tm_data = mysqli_fetch_assoc($tm_query);
$total_tm = $tm_data['total'] ?? 0;

$unidades_query = mysqli_query($conexion, "SELECT SUM(cantidad_unidades) as total FROM produccion p $condicion_kpi $condicion_fecha");
$unidades_data = mysqli_fetch_assoc($unidades_query);
$total_unidades = $unidades_data['total'] ?? 0;

$variedad_query = mysqli_query($conexion, "SELECT COUNT(DISTINCT producto_id) as total FROM produccion p $condicion_kpi $condicion_fecha");
$variedad_data = mysqli_fetch_assoc($variedad_query);
$total_variedad = $variedad_data['total'] ?? 0;

$activas_query = mysqli_query($conexion, "SELECT SUM(unidades_activas) as total FROM produccion p $condicion_kpi $condicion_fecha");
$activas_data = mysqli_fetch_assoc($activas_query);
$total_activas = $activas_data['total'] ?? 0;

$inactivas_query = mysqli_query($conexion, "SELECT SUM(unidades_inactivas) as total FROM produccion p $condicion_kpi $condicion_fecha");
$inactivas_data = mysqli_fetch_assoc($inactivas_query);
$total_inactivas = $inactivas_data['total'] ?? 0;

// --- LÓGICA DEL GRÁFICO ---
$nombres_graf = [];
$totales_graf = [];
$sql_graf = "SELECT e.nombre, SUM($formula_conversion) as total 
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

<style>
    :root { 
        --skillset-bg: #f3f3f3; 
        --skillset-card-radius: 24px;
        --skillset-dark: #5a0002;
        --skillset-blue: #3366ff;
    }
    #layoutSidenav_content { background-color: var(--skillset-bg); }
    .card { border-radius: var(--skillset-card-radius) !important; border: none !important; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 1.5rem; min-height: 140px; display: flex; flex-direction: column; justify-content: center; }
    .kpi-dark { background-color: var(--skillset-dark); color: white; }
    
    /* Asegura que las etiquetas y valores mantengan el color blanco en las tarjetas rojas */
    .kpi-dark .kpi-label, .kpi-dark .kpi-value { color: white !important; }

    .kpi-value { font-size: 1.8rem; font-weight: 700; margin-top: 5px; letter-spacing: -1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kpi-label { color: #fefefe; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-pill { border-radius: 30px !important; padding: 10px 20px; font-weight: 600; background: white; border: none; box-shadow: 0 4px 12px rgba(15, 119, 230, 0.05); transition: 0.3s; }
    .btn-pill:hover { background: #eeeeee; }

    .pagination .page-link { border: none; color: var(--skillset-dark); border-radius: 10px; margin: 0 3px; font-weight: 600; }
    .pagination .page-item.active .page-link { background-color: var(--skillset-dark); color: white; }

#fecha_inicio, #fecha_fin {
    color: #1a1a1a !important;
    background-color: #ffffff !important;
    border: 2px solid #dbdada !important;
    border-radius: 30px !important; 
    padding: 12px 20px !important; 
    transition: all 0.9s ease;
    height: auto; 
}

#fecha_inicio:focus, #fecha_fin:focus {
    border-color: #191919 !important;
    box-shadow: 0 0 10px rgba(51, 102, 255, 0.15);
    outline: none;
}

/* Asegura que el contenedor de la tabla no deje sobresalir las esquinas rectas */
.table-responsive {
    border-radius: 24px 24px 0 0; /* Ajusta el radio superior igual al de tus tarjetas */
    overflow: hidden;
}

/* Estilos de la cinta del encabezado */
.cinti {
    background-color: #5a000242;
    color: #5a0002;
}

/* Aplica el redondeado específicamente a las esquinas superiores de la tabla */
.table thead tr:first-child th:first-child {
    border-top-left-radius: 24px;
}
.table thead tr:first-child th:last-child {
    border-top-right-radius: 24px;
}

/* Añade esto dentro de tus etiquetas <style> */
.table tbody td.text-primary {
    color: #000022 !important;
}

.card h6.fw-bold {
    color: #000022 !important; 
}

.text-muted.small strong {
    color: #000022 !important; 
}
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-5">
            <div class="d-flex justify-content-between align-items-center mt-5 mb-5">
                <div>
                    <h2 class="fw-bold mb-1" style="letter-spacing: -1.5px; color: #000022;">REPORTE DE PRODUCCIÓN SEMANAL</h2>
                    <p class="text-muted small mb-0" >Bienvenido, <strong><?php echo $nombre_sesion; ?></strong></p>
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
                                $url = "index.php?empresa_id={$e['id']}";
                                if ($fecha_inicio) $url .= "&fecha_inicio=$fecha_inicio";
                                if ($fecha_fin) $url .= "&fecha_fin=$fecha_fin";
                                echo "<li><a class='dropdown-item' href='$url'>{$e['nombre']}</a></li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl col-md-6 mb-4">
                    <div class="card kpi-dark">
                        <div class="kpi-label">Total TM. Producidas</div>
                        <div class="kpi-value"><?php echo number_format($total_tm, 3, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-4">
                    <div class="card kpi-dark"> <div class="kpi-label">Unidades Producidas</div>
                        <div class="kpi-value"><?php echo number_format($total_unidades, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-4">
                    <div class="card kpi-dark"> <div class="kpi-label">Variedad Productos</div>
                        <div class="kpi-value"><?php echo $total_variedad; ?></div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-4">
                    <div class="card kpi-dark"> <div class="kpi-label">Prod. Activa</div>
                        <div class="kpi-value"><?php echo number_format($total_activas, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-xl col-md-6 mb-4">
                    <div class="card kpi-dark"> <div class="kpi-label">Prod. Inactiva</div>
                        <div class="kpi-value"><?php echo number_format($total_inactivas, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

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

            <div class="card mb-3 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="cinti">
                                <tr class="cinti">
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
                                $sql_tabla = "SELECT p.*, e.nombre as empresa, pr.nombre_producto,
                                            ($formula_conversion) as toneladas_reales
                                    FROM produccion p 
                                    INNER JOIN empresas e ON p.empresa_id = e.id 
                                    INNER JOIN productos pr ON p.producto_id = pr.id 
                                    $condicion_kpi $condicion_fecha 
                                    ORDER BY p.fecha_produccion DESC, p.id DESC 
                                    LIMIT $inicio_limit, $registros_por_pagina";

                                $query_t = mysqli_query($conexion, $sql_tabla);
                                while($row = mysqli_fetch_assoc($query_t)): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small"><?php echo date('d M, Y', strtotime($row['fecha_produccion'])); ?></td>
                                        <td class="fw-bold"><?php echo $row['empresa']; ?></td>
                                        <td><?php echo $row['nombre_producto']; ?></td>
                                        <td><?php echo number_format($row['cantidad_unidades'], 0, ',', '.'); ?></td>
                                        <td class="fw-bold text-primary"><?php echo number_format($row['toneladas_reales'], 3, ',', '.'); ?> TM</td>
                                        <td class="text-end pe-4"><span class="badge bg-light text-dark border-0 py-2 px-3" style="border-radius: 10px;">Procesado</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <nav class="mb-5 mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?pagina=<?php echo $pagina_actual-1; ?><?php echo $filtro_empresa ? "&empresa_id=$filtro_empresa" : ""; ?><?php echo $fecha_inicio ? "&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin" : ""; ?>">Anterior</a>
                        </li>
                    <?php endif; ?>

                    <?php for($i=1; $i<=$total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?pagina=<?php echo $i; ?><?php echo $filtro_empresa ? "&empresa_id=$filtro_empresa" : ""; ?><?php echo $fecha_inicio ? "&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin" : ""; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?pagina=<?php echo $pagina_actual+1; ?><?php echo $filtro_empresa ? "&empresa_id=$filtro_empresa" : ""; ?><?php echo $fecha_inicio ? "&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin" : ""; ?>">Siguiente</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </main>
</div>

<div class="modal fade" id="modalFecha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
      <form action="index.php" method="GET">
        <?php if($filtro_empresa): ?>
            <input type="hidden" name="empresa_id" value="<?php echo $filtro_empresa; ?>">
        <?php endif; ?>

        <div class="modal-body p-4">
            <h5 class="fw-bold mb-4">Filtrar por Periodo</h5>
            <div class="row mb-4">
                <div class="col-6">
                    <label class="small fw-bold text-muted mb-2 d-block">Desde</label>
                    <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control" placeholder="Seleccionar" value="<?php echo $fecha_inicio; ?>" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold text-muted mb-2 d-block">Hasta</label>
                    <input type="text" name="fecha_fin" id="fecha_fin" class="form-control" placeholder="Seleccionar" value="<?php echo $fecha_fin; ?>" required>
                </div>
            </div>
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
                backgroundColor: '#5a0002', 
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>