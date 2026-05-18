<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Inicio - Producción</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            /* Aplicamos el fondo al header con un overlay oscuro */
            .sb-topnav.navbar {
                background-color: #000022 !important;
                background-size: cover;
                background-position: center;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            /* Estilo del nombre de la marca */
            .navbar-brand {
                font-weight: 700;
                letter-spacing: -0.5px;
                color: #fff !important;
            }

            #btnNavbarSearch {
                background-color: #0c44ac !important; /* Color info */
                border: none;
            }

            .form-control {
                background-color: rgba(255, 255, 255, 0.1) !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
                color: #fff !important;
                border-radius: 8px 0 0 8px !important;
            }

            .form-control::placeholder {
                color: rgba(255, 255, 255, 0.5) !important;
            }

            /* Cambio de color para el icono de usuario en el menú superior */
            .navbar-nav .nav-link #navbarDropdown, 
            .navbar-nav .nav-link .fa-user {
                color: #ffffff !important; /* Cambiado de azul a blanco */
                transition: color 0.3s ease;
            }
            .navbar-nav .nav-link:hover .fa-user {
                color: #717d95 !important; /* Color rojo al pasar el cursor */
            }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark">
            <a class="navbar-brand ps-3" href="index.php">
                Reporte de producción <span class="text-info"></span>
            </a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
                <i class="fas fa-bars"></i>
            </button>
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Buscar..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user fa-fw"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown" style="border-radius: 12px; margin-top: 10px;">
                        <li><a class="dropdown-item" href="#!">Configuración</a></li>
                        <li><a class="dropdown-item" href="#!">Log de Actividad</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item text-danger fw-bold" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">