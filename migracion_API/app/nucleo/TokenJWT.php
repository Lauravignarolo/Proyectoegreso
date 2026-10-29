<?php

require_once RUTA_VISTA . "/RespuestaJSON.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * TokenJWT
 *
 * Fabricar y verificar tokens JWT que identifican al usuario y su rol, sin guardar nada en el servidor. 
 */
class TokenJWT
{
    /**
     * Recibe cédula y rol, y devuelve un JWT que vence a cierta hora.
     */
    public static function generar(string $cedula, string $rol): string
    {
        $time = time();

        $payload = [
            "cedula" => $cedula,
            "rol" => $rol,
            "iat" => $time,
            "exp" => $time + 3600 // Token vence en 1 hora
        ];

        return JWT::encode($payload, $_ENV["JWT_SECRET"], "HS256");
    }

    /**
     * Corta si el token es inválido (401) o si el rol no está autorizado (403), y devuelve los datos del token.
     */
    public static function exigirRol(string ...$roles)
    {
        $token = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
        
        // Pregunta si trae token de la cabecera Authorization, que debe tener el formato "Bearer"
        if (str_starts_with($token, "Bearer ")) {
            $token = substr($token, 7);
        } else {
            RespuestaJSON::error("No empieza con Bearer", 401);
        }

        // Chequea que el token es verdadero y no venció
        try {
            $decoded = JWT::decode($token, new Key($_ENV["JWT_SECRET"], "HS256"));
        } catch (Throwable $e) {
            RespuestaJSON::error("Token inválido", 401);
        }

        // Revisa que su rol sea válido
        if (!in_array($decoded->rol, $roles, true)) {
            RespuestaJSON::error("Rol no autorizado", 403);
        }
        return $decoded;
    }
}