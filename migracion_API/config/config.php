<?php

/**
 * Constantes de rutas y carga del entorno.
 *
 * Convención de carpetas:
 *   app/modelo       -> entidades y utilidades de persistencia
 *   app/modelo/dao   -> DAO (Data Access Object), uno por entidad
 *   app/servicio     -> servicios de negocio (reglas que no son CRUD)
 *   app/controlador  -> controladores, uno por recurso, prefijo "Procesar"
 *   app/vista        -> constructores de respuesta (JSON)
 *   app/nucleo       -> infraestructura transversal (conexión)
 *   app/rutas        -> tabla de ruteo de la API
 */

define("RUTA_RAIZ", dirname(__DIR__));
define("RUTA_APP", RUTA_RAIZ . "/app");
define("RUTA_MODELO", RUTA_APP . "/modelo");
define("RUTA_DAO", RUTA_MODELO . "/dao");
define("RUTA_SERVICIO", RUTA_APP . "/servicio");
define("RUTA_CONTROLADOR", RUTA_APP . "/controlador");
define("RUTA_VISTA", RUTA_APP . "/vista");
define("RUTA_NUCLEO", RUTA_APP . "/nucleo");
define("RUTA_RUTAS", RUTA_APP . "/rutas");
define("RUTA_PUBLIC", RUTA_RAIZ . "/public");

/**
 * Carga un archivo .env sin necesitar Composer.
 *
 * En la intranet puede no existir la carpeta vendor/ o el servidor puede
 * no tener permisos para leerla. Este cargador cubre ese caso: lee el
 * .env con PHP puro y no corta la ejecución si el archivo no está.
 */
function cargarEntorno(string $ruta): void
{
    if (!is_file($ruta) || !is_readable($ruta)) {
        return;
    }

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lineas === false) {
        return;
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === "" || str_starts_with($linea, "#")) {
            continue;
        }

        $partes = explode("=", $linea, 2);

        if (count($partes) !== 2) {
            continue;
        }

        $clave = trim($partes[0]);
        $valor = trim($partes[1]);

        if ($clave === "") {
            continue;
        }

        // Quita las comillas simples o dobles que envuelven al valor.
        if (strlen($valor) >= 2) {
            $primero = $valor[0];
            $ultimo = $valor[strlen($valor) - 1];

            if (($primero === '"' && $ultimo === '"')
                || ($primero === "'" && $ultimo === "'")
            ) {
                $valor = substr($valor, 1, -1);
            }
        }

        // No pisa variables que ya vengan definidas por el servidor.
        if (!array_key_exists($clave, $_ENV)) {
            $_ENV[$clave] = $valor;

            if (function_exists("putenv")) {
                putenv("{$clave}={$valor}");
            }
        }
    }
}

$autoload = RUTA_RAIZ . "/vendor/autoload.php";

// Si vendor/ está disponible se usa phpdotenv; si no, el cargador propio.
if (is_readable($autoload)) {
    require_once $autoload;
}

if (class_exists("Dotenv\\Dotenv")) {
    try {
        \Dotenv\Dotenv::createImmutable(RUTA_RAIZ)->safeLoad();
    } catch (\Throwable $error) {
        error_log("[Config] No se pudo leer .env con Dotenv: " . $error->getMessage());
        cargarEntorno(RUTA_RAIZ . "/.env");
    }
} else {
    cargarEntorno(RUTA_RAIZ . "/.env");
}
