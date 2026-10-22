
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/Login.php";

class ProcesarLogin
{
    public function autenticar(string $metodo): void
    {
        if ($metodo !== "POST") {
            header("Allow: POST");
            RespuestaJson::error("Método no permitido", 405);
        }

        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJson::error("JSON inválido", 400);
        }

        $cedula = trim($datos["cedula"] ?? "");
        $clave = $datos["clave"] ?? "";

        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula) ||
            !is_string($clave) || $clave === "") {
            RespuestaJson::error("Credenciales inválidas", 422);
        }

        $conector = new ConectorPDO(
            $_ENV["DB_HOST"],
            $_ENV["DB_USUARIO"],
            $_ENV["DB_CLAVE"],
            $_ENV["DB_NOMBRE"],
            (int) ($_ENV["DB_PUERTO"] ?? 3306)
        );

        $conexion = $conector->establecerConexion();
        $dao = new AccesoDatosUsuario($conexion);
        $login = new Login($dao);

        $usuario = $login->autenticar($cedula, $clave);

        $conector->desconectar();

        if ($usuario === null) { 
            RespuestaJson::error("Cédula o contraseña incorrecta", 401); 
        } 
        
        if (!$usuario->getEstado()) { 
            RespuestaJson::error("La cuenta está desactivada", 403); 
        }

        session_regenerate_id(true);

        $_SESSION["cedula"] = $usuario->getCedula();
        $_SESSION["administrador"] = $usuario->esAdministrador();
        $_SESSION["docente"] = $usuario->esDocente();
        $_SESSION["direccion"] = $usuario->esDireccion();
        $_SESSION["tecnico"] = $usuario->esTecnico();

        $_SESSION["csrfToken"] = bin2hex(random_bytes(32));
        

        $rol = match (true) {
            $usuario->esAdministrador() => "administrador",
            $usuario->esDocente() => "docente",
            $usuario->esTecnico() => "tecnico",
            $usuario->esDireccion() => "direccion",
            default => null
        };

        if ($rol === null) {
            $_SESSION = [];
            session_destroy();
            RespuestaJson::error("Usuario sin rol autorizado", 403);
        }

        RespuestaJson::exito([
            "cedula" => $usuario->getCedula(),
            "rol" => $rol,
            "csrfToken" => $_SESSION["csrfToken"]
        ]);
    }

    public function cerrarSesion(string $metodo): void
    {
        if ($metodo !== "POST") {
            header("Allow: POST");
            RespuestaJson::error("Método no permitido", 405);
        }

        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (!isset($_SESSION["cedula"]) ||
            !isset($_SESSION["csrfToken"]) ||
            !is_string($token) ||
            !hash_equals($_SESSION["csrfToken"], $token)) {
            RespuestaJson::error("Sesión o token inválido", 403);
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires" => time() - 3600,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => $params["samesite"] ?? "Lax"
            ]);
        }

        session_destroy();

        RespuestaJson::exito(["mensaje" => "Sesión cerrada"]);
    }
}
