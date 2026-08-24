<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosSalones.php";

$conectorPDO = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conectorPDO->establecerConexion();

$accesoDatosSalones = new AccesoDatosSalones($conexion);

$salones = $accesoDatosSalones->listarSalones();

$conectorPDO->desconectar();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>S.G.R.S.I</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link
        rel="stylesheet"
        href="../../public/assets/css/general.css">

    <link
        rel="stylesheet"
        href="../../public/assets/bootstrap/css/bootstrap.min.css">

    <link
        rel="stylesheet"
        href="../../public/assets/css/salones.css">

</head>

<body id="inicio">

    <header class="HeaderChico">

        <img
            src="../../public/assets/img/iti utu.png"
            alt=""
            height="100px">

        <h1>S.G.R.S.I</h1>

        <a href="tecnico.php">Volver</a>

    </header>


    <nav class="NavBuscar">

        <form class="FormBuscar">

            <fieldset>

                <input
                    type="search"
                    id="BuscarSalon"
                    placeholder="Buscar salón...">

                <button type="submit">
                    <i class="bi bi-search"></i>
                </button>

            </fieldset>

        </form>

    </nav>


    <main>

        <section class="SectionSalones">

            <table class="TableSalones">

                <thead>

                    <tr>
                        <th>Tipo de salón</th>
                        <th>Número de salón</th>
                    </tr>

                </thead>


                <tbody
                    class="TbodySalones"
                    id="TbodySalones">

                    <?php foreach ($salones as $salon): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($salon["tipo_de_salon"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($salon["numero_de_salon"]) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </section>

    </main>


    <a href="#inicio" class="ASubir">

        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="35"
            height="35"
            fill="currentColor"
            class="bi bi-arrow-up-circle"
            viewBox="0 0 16 16">

            <path
                fill-rule="evenodd"
                d="M1 8a7 7 0 1 0 14 0A8 8 0 0 1 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3 3a.5.5 0 0 1 .708.708L8.5 5.707z" />

        </svg>

    </a>


    <footer>

        <p>
            &copy; 2026 SGRSI. Todos los derechos reservados.
        </p>

    </footer>


    <script src="../../public/assets/bootstrap/js/bootstrap.min.js"></script>

</body>

</html>