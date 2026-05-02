<?php
session_start();
include("conexion.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);

    $query = "SELECT id, nombre FROM empresas WHERE usuario = '$usuario' AND password = '$password' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if (mysqli_num_rows($resultado) > 0) {
        $datos = mysqli_fetch_assoc($resultado);
        $_SESSION['empresa_id'] = $datos['id'];
        $_SESSION['empresa_nombre'] = $datos['nombre'];
        header("Location: index"); 
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Producción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        :root { 
            --card-radius: 30px; /* Aumentamos un poco el radio para que se vea más moderno al ser más grande */
            --dark-skillset: #1a1a1a; 
        }

        body { 
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('img/fondo2.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            font-family: 'Inter', sans-serif; 
            height: 100vh;
        }

        .card { 
            border-radius: var(--card-radius) !important; 
            border: none !important; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.5) !important; 
            /* AJUSTE DE TAMAÑO: Aumentamos el padding interno */
            padding: 40px; 
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .card-header { 
            background: transparent !important; 
            border: none !important; 
            font-weight: 700; 
            /* AJUSTE: Título más grande */
            font-size: 1.8rem; 
            color: var(--dark-skillset); 
            margin-bottom: 20px;
        }

        .form-control { 
            border-radius: 15px !important; 
            background-color: #f8f9fa !important; 
            border: 1px solid #dee2e6 !important; 
            /* AJUSTE: Inputs más altos */
            padding: 15px 20px; 
        }

        .btn-primary { 
            background-color: var(--dark-skillset) !important; 
            border: none !important; 
            border-radius: 30px !important; 
            padding: 15px !important; 
            font-weight: 600; 
            font-size: 1.1rem;
            transition: 0.3s; 
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .logo-text { 
            font-size: 3rem; 
            margin-bottom: 30px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <!-- AJUSTE DE ANCHO: Cambiado de col-lg-4 a col-lg-5 -->
            <div class="col-lg-5 col-md-8">
                <div class="text-center">
                    <div class="logo-text"><span class="text-info">☻</span></div>
                </div>
                <div class="card">
                    <div class="card-header text-center">Inicio de sesión</div>
                    <div class="card-body">
                        <?php if($error != ""): ?>
                            <div class="alert alert-danger small border-0" style="border-radius: 12px;">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="login">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Usuario</label>
                                <input name="usuario" type="text" class="form-control" placeholder="nombre_user" required />
                            </div>
                            <div class="mb-5">
                                <label class="form-label small fw-bold text-muted">Contraseña</label>
                                <input name="password" type="password" class="form-control" placeholder="••••••••" required />
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>