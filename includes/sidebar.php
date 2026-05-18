<div id="layoutSidenav_nav">
    <style>
        /* Estilos para los títulos de sección (Principal e Inicio) */
        .sb-sidenav-dark .sb-sidenav-menu .sb-sidenav-menu-heading {
            font-size: 0.95rem !important; /* Más grande */
            font-weight: 700 !important;
            letter-spacing: 0.05rem;
            margin-top: 1.5rem;
        }

        /* Efecto de fondo blanco de izquierda a derecha al pasar el cursor */
        .sb-sidenav-dark .sb-sidenav-menu .nav-link {
            position: relative;
            z-index: 1;
            transition: color 0.4s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.22) 50%, transparent 50%);
            background-size: 200% 100%;
            background-position: right bottom;
            border-radius: 0 50px 50px 0; /* Bordes redondeados a la derecha */
            margin-right: 10px;
        }

        .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover {
            color: #fff !important;
            background-position: left bottom; /* Desplaza el fondo hacia la derecha */
        }

        /* Ajuste para que los iconos no se pierdan */
        .nav-link:hover .sb-nav-link-icon {
            color: #fff !important;
        }

        /* Nueva clase personalizada para el texto e icono del usuario en el footer */
        .text-custom-footer {
            color: #cddfff !important; 
        }
    </style>

    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion" style="background-color: #000022; background-size: cover; background-position: center;">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading" style="color: rgb(255, 255, 255);">PRINCIPAL</div>
                <a class="nav-link" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Inicio
                </a>

                <div class="sb-sidenav-menu-heading" style="color: rgb(255, 255, 255);">INICIO</div>
                <a class="nav-link" href="registro_produccion.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-plus-circle"></i></div>
                    Cargar Production
                </a>
            </div>
        </div>
        
        <div class="sb-sidenav-footer" style="background-color: #000022; border-top: 1px solid rgba(255,255,255,0.1); padding: 35px;">
            <div class="small" style="color: rgb(255, 255, 255); font-size: 1rem; margin-bottom: 5px;">
                Sesión iniciada como:
            </div>
            <div class="fw-bold text-custom-footer" style="font-size: 1.0rem; letter-spacing: 0.5px;">
                <i class="fas fa-user-circle me-2"></i>
                <?php 
                    echo isset($_SESSION['empresa_nombre']) ? $_SESSION['empresa_nombre'] : "Invitado"; 
                ?>
            </div>
        </div>
    </nav>
</div>