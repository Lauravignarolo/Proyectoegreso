<?php

/**
 * Constantes de rutas de la aplicación.
 *
 * Convención de carpetas:
 *   app/modelo       -> entidades y utilidades de persistencia
 *   app/modelo/dao   -> DAO (Data Access Object), uno por entidad
 *   app/servicio     -> servicios de negocio (reglas que no son CRUD)
 *   app/controlador  -> controladores, uno por recurso, prefijo "Procesar"
 *   app/vista        -> constructores de respuesta (JSON)
 *   app/nucleo       -> infraestructura transversal (sesión, token, conexión)
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

require_once RUTA_RAIZ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(RUTA_RAIZ);
$dotenv->load();
