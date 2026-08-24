<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> S.G.R.S.I </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/assets/css/general.css">
    <link rel="stylesheet" href="../../public/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/assets/css/tickets.css">
</head>

<body id="inicio">
    <header class="HeaderChico">
        <img src="assets/img/iti utu.png" alt="" height="100px">
        <h1> S.G.R.S.I </h1>
        <a href="../vista/tecnico.php">Volver</a>
    </header>
    <nav class="NavBuscar">
        <form class="FormBuscar">
            <fieldset>
                <input type="search" placeholder="Buscar...">
                <button type="submit"><i class="bi bi-search"></i></button>
            </fieldset>
        </form>
    </nav>
    <main>
        <section class="SectionTickets">
            <article class="ArticleTickets">
                <header>
                    <h3>Tickets Actuales</h3>
                </header>
                <table class="TableTickets">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Docente</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Tipo de Salón</th>
                            <th>N° Salón</th>
                            <th>N° Equipo</th>
                            <th>Asignatura</th>
                            <th>Grupo</th>
                            <th>Turno</th>
                            <th>Estado del Equipo</th>
                            <th>Urgencia</th>
                        </tr>
                    </thead>
                    <tbody class="TbodyTickets">

    <?php if (empty($tickets)): ?>

        <tr>
            <td colspan="12">
                No hay tickets registrados.
            </td>
        </tr>

    <?php else: ?>

        <?php foreach ($tickets as $ticket): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($ticket["estudiante_a_cargo"]) ?>
                </td>

                <td>
    <?= htmlspecialchars(
        $ticket["nombre"] . " " . $ticket["apellido"]
    ) ?>
</td>

                <td>
                    <?= htmlspecialchars($ticket["hora_de_entrada"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["hora_de_salida"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["tipo_de_salon"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["numero_de_salon"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["numero_de_equipo"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["asignatura"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["grupo"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["turno"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["estado"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ticket["urgencia"] ?? "Sin especificar") ?>
                </td>

            </tr>

        <?php endforeach; ?>

    <?php endif; ?>

                    </tbody>
                </table>
            </article>
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
    <script src="../../public/assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>