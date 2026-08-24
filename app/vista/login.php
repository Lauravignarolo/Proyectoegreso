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
    <link rel="stylesheet" href="../../public/assets/css/login.css">
    <link rel="stylesheet" href="../../public/assets/css/formularios.css">
    <link rel="stylesheet" href="../../public/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

</head>

<body id="inicio">

     <header class="HeaderInicio">
        <img src="/public/assets/img/iti utu.png" alt="">
        <h1> S.G.R.S.I </h1>
        <a href="../../public/index.html">Volver</a>
    </header>
    <nav>
        <a class="AInicio" href="index.html"></a>
    </nav>
    <main>
        <Section class="SectionFormularioLogin">
    
 <main>
        <Section class="SectionFormularioLogin">
           <form action="procesarLogin.php" method="POST">
                <fieldset>
                    <legend> INICIAR SESION </legend>

                    
                        <label for="">Usuario</label>
                     <div>
                        <label class="LabelIconoUser" for="cedula">
                            <svg xmlns="http://www.w3.org/2000/svg"
         width="30"
         height="30"
         fill="currentColor"
         viewBox="0 0 16 16">
        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
    </svg>
                        </label>
                        <input class="Inputllenar user" type="text" id="cedula" name="cedula" pattern="[1-9][0-9]{7}"
                            title="Ingrese exactamente 8 dígitos sin puntos ni guiones" inputmode="numeric"
                            maxlength="8" required placeholder="Usuario">

                    </div>
                    
                        <label for="">Contraseña</label>
                    <div Class="DivPassword">
                        <label class="LabelIconoPassword" for="Clave" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                                fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4M4.5 7A1.5 1.5 0 0 0 3 8.5v5A1.5 1.5 0 0 0 4.5 15h7a1.5 1.5 0 0 0 1.5-1.5v-5A1.5 1.5 0 0 0 11.5 7zM8 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3" />
                            </svg>
                        </label>
                        <input class="Inputllenar password" id="clave" name="clave" type="password" minlength="8" autocomplete="current-password"
                            title="Ingrese mas de 8 caracteres" required placeholder="Contraseña">

                        <label class="LabelCerradoIcon">
                            <i id="ojo" class="bi bi-eye-slash"></i>
                        </label>

                    </div>
  <?php
                    echo "<p>" . ($_GET["error"] ?? "") . "</p>";
                ?>
                    <a class="AIngresar"
                       ><button type="submit" class="ButtonIngresar">INGRESAR</button>
                    </a>

                </fieldset>

            </form>
        </Section>
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
    <script src="/public/assets/js/login.js"></script>
</body>

</html>