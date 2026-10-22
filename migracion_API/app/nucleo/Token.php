<?php

require_once RUTA_VISTA . "/RespuestaJSON.php";

/**
 * Token anti-CSRF.
 *
 * La sesión se transporta en una cookie: si un sitio de terceros pudiera
 * hacer que el navegador envíe esa cookie, podría disparar un POST en
 * nombre del usuario. El token CSRF lo evita, porque el navegador nunca
 * lo manda solo: el JavaScript de la propia aplicación lo lee y lo coloca
 * en la cabecera X-CSRF-Token.
 *
 * Por eso el token solo se exige en los verbos que modifican datos
 * (POST, PUT, PATCH, DELETE). En GET no hay efecto, así que se permite
 * consultar sin token.
 */
class Token
{
    /**
     * Devuelve el token CSRF de la sesión, creándolo si todavía no existe.
     */
    public static function generar(): string
    {
        if (!isset($_SESSION["csrfToken"]) || !is_string($_SESSION["csrfToken"])) {
            $_SESSION["csrfToken"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["csrfToken"];
    }

    /**
     * Corta la petición si el token recibido no coincide con el de la
     * sesión (403).
     */
    public static function verificar(): void
    {
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (
            !isset($_SESSION["csrfToken"]) ||
            !is_string($token) ||
            $token === "" ||
            !hash_equals((string) $_SESSION["csrfToken"], $token)
        ) {
            RespuestaJSON::error("Token CSRF inválido", 403);
        }
    }
}
