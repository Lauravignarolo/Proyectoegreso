<?php

require_once RUTA_VISTA . "/RespuestaJSON.php";

/**
 * Punto único de acceso a la base de datos.
 *
 * Todos los DAO reciben la conexión desde acá, de modo que las credenciales
 * (que viven en el .env) nunca salen del núcleo y ningún controlador
 * necesita conocer el DSN ni las variables de entorno.
 */
class Conexion
{
    private static ?PDO $conexion = null;

    /**
     * Devuelve la conexión PDO, creándola si todavía no existe.
     */
    public static function conectar(): PDO
    {
        if (self::$conexion instanceof PDO) {
            return self::$conexion;
        }

        $host = $_ENV["DB_HOST"] ?? "localhost";
        $puerto = (int) ($_ENV["DB_PUERTO"] ?? 3306);
        $nombre = $_ENV["DB_NOMBRE"] ?? "";
        $usuario = $_ENV["DB_USUARIO"] ?? "";
        $clave = $_ENV["DB_CLAVE"] ?? "";

        $dsn = "mysql:host={$host};port={$puerto};dbname={$nombre};charset=utf8mb4";

        try {
            self::$conexion = new PDO($dsn, $usuario, $clave, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $error) {
            error_log("[Conexion] " . $error->getMessage());

            RespuestaJSON::error("Error de conexión con la base de datos.", 500);
        }

        return self::$conexion;
    }

    /**
     * Cierra la conexión. La conexión real se cierra sola al terminar
     * el script; esto solo libera la referencia del objeto PDO.
     */
    public static function desconectar(): void
    {
        self::$conexion = null;
    }
}
