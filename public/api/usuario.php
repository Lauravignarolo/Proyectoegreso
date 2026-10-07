<?php
require_once __DIR__ . "/../../config/config.php";
require_once RUTA_CONTROLADOR . "/ProcesarUsuario.php";

session_start();

$controlador = new UsuarioController();
$controlador->gestionar($_SERVER["REQUEST_METHOD"]);
