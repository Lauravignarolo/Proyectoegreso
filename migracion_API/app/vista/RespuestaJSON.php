<?php

/**
 * Constructores de las respuestas de la API.
 *
 * Todas las respuestas del backend salen de acá, así se garantiza que
 * el formato sea uniforme:
 *
 *   Exito  -> HTTP 2xx  -> { "datos": ... }
 *   Error  -> HTTP 4xx/5xx -> { "mensaje": "..." }
 *
 * Ningún mensaje de error interno (mensajes de PDO, stack traces, rutas de
 * archivo) llega al cliente: eso se registra en el log del servidor.
 */
class RespuestaJSON
{
    /**
     * Responde con los datos de la operación exitosa.
     *
     * @param mixed $datos Cuerpo de la respuesta.
     * @param int   $estado Código HTTP (200 por defecto, 201 al crear).
     */
    public static function exito(mixed $datos = null, int $estado = 200): void
    {
        http_response_code($estado);
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode(["datos" => $datos], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Responde con un error controlado (validaciones, permisos, no encontrados).
     *
     * @param string $mensaje Mensaje pensado para el usuario final.
     * @param int    $estado  Código HTTP (400, 401, 403, 404, 409, 422, 500).
     */
    public static function error(string $mensaje, int $estado): void
    {
        http_response_code($estado);
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode(["mensaje" => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Responde 405 e informa en la cabecera Allow qué verbos sí se aceptan.
     *
     * @param string $permitidos Lista de verbos separados por coma. Ej: "GET, POST".
     */
    public static function metodoNoPermitido(string $permitidos): void
    {
        header("Allow: " . $permitidos);

        self::error("Método no permitido", 405);
    }
}
