<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;

class AutorizacionJWT {

    public static function verificar($token) {


    $config = require __DIR__ . "/../../config/secretos.php";

    $claveSecreta = $config["secreto"];

    try {
    $claims = JWT::decode(
    $token,
    new Key($claveSecreta, "HS256")
    );
    // La verificación fue exitosa
    } catch (SignatureInvalidException $e) {
    // La MAC no coincide: token alterado o clave incorrecta
    http_response_code(401);
    } catch (ExpiredException $e) {
    // El token venció
    http_response_code(401);
    } catch (\UnexpectedValueException $e) {
    // Otros problemas de formato o validación del token
    http_response_code(401);
    }

    exit;

}
}