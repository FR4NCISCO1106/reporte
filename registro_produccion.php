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

// Consultamos productos vinculados a la empresa asignada
$productos_query = mysqli_query($conexion, "SELECT id, nombre_producto FROM productos WHERE empresa_id = '$empresa_id'");

// Guardamos los productos en un array PHP para pasárselos a JavaScript de forma segura
$productos_array = [];
if ($productos_query && mysqli_num_rows($productos_query) > 0) {
    while($p = mysqli_fetch_assoc($productos_query)) {
        $productos_array[] = [
            'id' => $p['id'],
            'nombre' => $p['nombre_producto']
        ];
    }
}
?>

<style>
    :root { 
        --skill-bg: #f4f4f7; 
        --skill-card: #ffffff; 
        --skill-dark: #000022; 
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

    .btn-save:hover{
    background-color: #004e89;
    color: #fff;
    }

    .text-muted strong {
    color: #000022 !important;
    }

    /* Forzar a que el texto del placeholder sea visible sobre el fondo claro del input */
    #buscar_producto::placeholder {
        color: #757575 !important;
        opacity: 1 !important; 
    }

    /* ESTILOS EXCLUSIVOS PARA EL MENÚ FLOTANTE DEL BUSCADOR */
    .contenedor-busqueda {
        position: relative;
    }
    .lista-sugerencias {
        position: absolute;
        width: 100%;
        max-height: 220px;
        overflow-y: auto;
        z-index: 1050;
        background: #ffffff;
        border: 1px solid #1b1b1b20;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-top: 5px;
    }
    .lista-sugerencias option {
        padding: 12px 20px;
        cursor: pointer;
        transition: background 0.2s ease;
        font-size: 0.9rem;
        color: #495057;
    }
    .lista-sugerencias option:hover {
        background-color: #f0f1f4;
        color: var(--skill-dark);
    }
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-md-5"> <div class="py-5">
                <h2 class="fw-bold mb-1" style="letter-spacing: -1.5px; font-size: 2.2rem; color: #000022;">Cargar Producción</h2>
                <p class="text-muted">Empresa: <strong><?php echo $empresa_nombre; ?></strong></p>
            </div>

            <div class="skill-card">
                <form id="form_produccion" action="guardar_produccion.php" method="POST">
                    
                    <div class="section-header">
                        <span class="section-title">Detalle del Producto</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-4 contenedor-busqueda">
                            <label class="form-label">Producto</label>
                            
                            <input type="text" id="buscar_producto" class="form-control" placeholder="Escribe para buscar producto..." autocomplete="off" required>
                            
                            <input type="hidden" name="producto_id" id="producto_id" required>
                            
                            <div id="lista_sugerencias" class="lista-sugerencias" style="display: none;"></div>
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
    
    <script>
        const productos = <?php echo json_encode($productos_array); ?>;
        
        const inputBuscar = document.getElementById('buscar_producto');
        const inputId = document.getElementById('producto_id');
        const divSugerencias = document.getElementById('lista_sugerencias');
        const formProduccion = document.getElementById('form_produccion');

        inputBuscar.addEventListener('input', function() {
            const textoBuscado = this.value.toLowerCase().trim();
            divSugerencias.innerHTML = ''; 
            
            if (textoBuscado === '') {
                divSugerencias.style.display = 'none';
                inputId.value = '';
                return;
            }

            // CAMBIO AQUÍ: Si el usuario cambia el texto manualmente, invalidamos el ID anterior 
            // hasta que vuelva a seleccionar una opción válida de la lista.
            inputId.value = '';

            const productosFiltrados = productos.filter(p => p.nombre.toLowerCase().includes(textoBuscado));

            if (productosFiltrados.length > 0) {
                divSugerencias.style.display = 'block';

                productosFiltrados.forEach(p => {
                    const opcion = document.createElement('option');
                    opcion.value = p.id;
                    opcion.textContent = p.nombre;

                    opcion.addEventListener('click', function() {
                        inputBuscar.value = p.nombre; 
                        inputId.value = p.id;         
                        divSugerencias.style.display = 'none'; 
                    });

                    divSugerencias.appendChild(opcion);
                });
            } else {
                divSugerencias.style.display = 'block';
                const sinResultados = document.createElement('option');
                sinResultados.textContent = 'No se encontraron coincidencias';
                sinResultados.disabled = true;
                sinResultados.style.color = '#999';
                divSugerencias.appendChild(sinResultados);
                inputId.value = ''; 
            }
        });

        // MEJORA: Validar antes de enviar el formulario para prevenir el error de SQL
        formProduccion.addEventListener('submit', function(e) {
            if (inputId.value === '' || inputId.value === null) {
                e.preventDefault(); // Detiene el envío
                alert('Por favor, selecciona un producto válido de la lista de sugerencias.');
                inputBuscar.focus();
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target !== inputBuscar && e.target !== divSugerencias) {
                divSugerencias.style.display = 'none';
            }
        });
    </script>

    <?php 
    include("includes/footer.php"); 
    ?>
</div>