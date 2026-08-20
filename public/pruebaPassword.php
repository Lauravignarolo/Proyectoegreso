<?php

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

require_once __DIR__ . "/../app/modelo/ConectarPDO.php";

$conector = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conector->establecerConexion();

$sql = "SELECT * FROM USUARIO";

$consulta = $conexion->query($sql);

$usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
var_dump($usuarios);
echo "</pre>";
