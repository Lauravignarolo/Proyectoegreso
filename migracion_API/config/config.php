
<?php

define("RUTA_RAIZ", dirname(__DIR__));
define("RUTA_APP", RUTA_RAIZ . "/app");
define("RUTA_MODELO", RUTA_APP . "/modelo");
define("RUTA_CONTROLADOR", RUTA_APP . "/controlador");
define("RUTA_VISTA", RUTA_APP . "/vista");
define("RUTA_NUCLEO", RUTA_APP . "/nucleo");
define("RUTA_RUTAS", RUTA_APP . "/rutas");
define("RUTA_PUBLIC", RUTA_RAIZ . "/public");

require_once RUTA_RAIZ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(RUTA_RAIZ);
$dotenv->load();
