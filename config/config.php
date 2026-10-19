
<?php

// Raíz de la migración API
define("RUTA_RAIZ", dirname(__DIR__, 2));

// Rutas de las capas
define("RUTA_APP", RUTA_RAIZ . "/app");
define("RUTA_MODELO", RUTA_APP . "/modelo");
define("RUTA_CONTROLADOR", RUTA_APP . "/controlador");
define("RUTA_VISTA", RUTA_APP . "/vista");
define("RUTA_NUCLEO", RUTA_APP . "/nucleo");
define("RUTA_RUTAS", RUTA_APP . "/rutas");
define("RUTA_PUBLIC", RUTA_APP . "/public");

// Composer está en Proyectoegreso/vendor
require_once dirname(RUTA_RAIZ) . "/vendor/autoload.php";

// El archivo .env está en Proyectoegreso/.env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(RUTA_RAIZ));
$dotenv->load();
