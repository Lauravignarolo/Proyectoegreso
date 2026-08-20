<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> S.G.R.S.I </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/general.css">
    <link rel="stylesheet" href="../../public/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/assets/css/formularios.css">
    <link rel="stylesheet" href="../../public/assets/css/administrador.css">
</head>

<body id="inicio">
    <header class="HeaderChico">
        <img src="assets/img/iti utu.png" alt="" height="100px">
        <h1> S.G.R.S.I </h1>
        <a href="administrador.php">Volver</a>
    </header>
    <main>
        
<section class="seccionTablaEmpleados">
            <header class="cajaEncabezado">
                <h2>Datos de empleados</h2>

                <button type="button" class="btnOperacion" id="btnAltaEmpleado">
                    Alta de empleado
                </button>
            </header>

            <?php //Mejorar cosméticamente en un futuro este tipo de captura información con GET ?>
            <?= htmlspecialchars($_GET["error"] ?? "") ?>
            <?= htmlspecialchars($_GET["resultado"] ?? "") ?>

            <table>
                <caption>
                    Listado de empleados registrados
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Cédula</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Apellido</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpoTablaEmpleados">
                    <?php foreach ($usuarios as $usuario) { ?>

                        <?php
                            $roles = "";

                            if ($usuario["administrador"] == 1) {
                                $roles = "Administrador";
                            }

                            if ($usuario["logistica"] == 1) {
                                if ($roles != "") {
                                    $roles = $roles . ", ";
                                }

                                $roles = $roles . "Logística";
                            }

                            if ($roles == "") {
                                $roles = "Sin rol";
                            }
                            /* <?php echo $variable ?> equivalente a <?= $variable ?>*/ 
                        ?>

                        <tr>
                            <td><?= htmlspecialchars($usuario["cedula"]) ?></td>
                            <td><?= htmlspecialchars($usuario["nombre"]) ?></td>
                            <td><?= htmlspecialchars($usuario["apellido"]) ?></td>
                            <td><?= htmlspecialchars($roles) ?></td>

                            <td>
                                <div class="cajaOperaciones">
                                    <button type="button" class="btnOperacion btnModificar">Modificar</button>
                                    
                                    <form action="procesarBajaUsuario.php" method="post" class="formularioEliminarEmpleado">
                                        <input type="hidden" name="cedula" value="<?=htmlspecialchars($usuario["cedula"])?>">
                                        <input type="hidden" name="csrfToken" value="<?=htmlspecialchars($_SESSION["csrfToken"])?>">
                                        <button type="submit" class="btnOperacion btnEliminar">Eliminar</button>
                                    </form>
                                    
                                    
                                </div>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </section>
</main>
    <a href="#inicio" class="ASubir">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-arrow-up-circle"
            viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z" />
        </svg>
    </a>

    <footer>
        <p>&copy; 2026 SGRSI. Todos los derechos reservados.</p>
    </footer>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>

</html>