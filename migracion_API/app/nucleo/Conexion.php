<?php
require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class Conexion {
    public static function conectar(): PDO
    {
        try {
            $conectorPDO = ConectorPDO::obtenerInstancia($_ENV['DB_HOST'], (int) $_ENV['DB_PUERTO'], $_ENV['DB_USUARIO'], $_ENV['DB_CLAVE'], $_ENV['DB_NOMBRE']);
            return $conectorPDO->establecerConexion();
        } catch (PDOException $error) {
            error_log($error->getMessage()); //Error para verificar por consola
            RespuestaJSON::error("Error de conexión con la base de datos.", 500);
        }
    }

    public static function desconectar(): void
    {
        $conectorPDO = ConectorPDO::obtenerInstancia($_ENV['DB_HOST'], (int) $_ENV['DB_PUERTO'], $_ENV['DB_USUARIO'], $_ENV['DB_CLAVE'], $_ENV['DB_NOMBRE']);
        $conectorPDO->desconectar();
    }
}