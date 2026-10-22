<?php
require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/ConectorDAO.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Sesion.php";

class UsuarioController
{

    public function gestionar(string $metodo): void
    {
        Sesion::verificarRol("administrador");

        //Forma de controlar a través del token las consultas críticas sobre determinados datos
        //Puesto que CSRF es para proteger la manipulación de los datos, se permite operar sobre GET sin token
        if (in_array($metodo, ["POST", "PUT", "DELETE"], true)) {
            Token::verificarCSRF();
        }

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->alta(),
            "PUT" => $this->modificar(),
            "DELETE" => $this->baja(),
            default => RespuestaJSON::error("Método no permitido", 405),
        };
    }

    private function listar(): void
    {
        if (isset($_GET["cedula"])) {
            //En caso de recibir datos mediante la superglobal, se busca un usuario específico
            $this->listarUsuario(trim($_GET["cedula"]));
        } else {
            $this->listarUsuarios();
        }
        
    }

    private function listarUsuario(string $cedula): void
    {
        if ($cedula === "") {
            RespuestaJSON::error("La cédula es obligatoria", 400);
        }

        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
            RespuestaJSON::error("Cédula incorrecta", 422);
        }

        $conexion = Conexion::conectar();
        $dao = new UsuarioDAO($conexion);
        $usuario = $dao->listarUsuario($cedula);
        unset($dao, $conexion);
        Conexion::desconectar();

        if ($usuario === null) {
            RespuestaJSON::error("El usuario no existe", 404);
        }

        RespuestaJSON::exito($usuario);
    }

    private function listarUsuarios(): void
    {
        $conexion = Conexion::conectar();
        $dao = new UsuarioDAO($conexion);
        $usuarios = $dao->listarUsuarios();
        unset($dao, $conexion);
        Conexion::desconectar();
        RespuestaJSON::exito($usuarios);
        
    }

    private function alta(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
        }

        $cedula = trim($datos["cedula"] ?? "");
        $nombre = trim($datos["nombre"] ?? "");
        $apellido = trim($datos["apellido"] ?? "");
        $clave = $datos["clave"] ?? "";
        $confirmarClave = $datos["confirmarClave"] ?? "";
        $rol = trim($datos["rol"] ?? "");

        if ($cedula === "" || $nombre === "" || $apellido === "" || $clave === "" || $confirmarClave === "" || $rol === "") {
            RespuestaJSON::error("Existen campos vacíos", 422);
        }
        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
            RespuestaJSON::error("Cédula incorrecta", 422);
        }
        if (strlen($clave) < 12) {
            RespuestaJSON::error("La contraseña debe contener al menos 12 caracteres", 422);
        }
        if ($clave !== $confirmarClave) {
            RespuestaJSON::error("Las contraseñas ingresadas no coinciden", 422);
        }

        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $conexion = Conexion::conectar();
        $dao = new UsuarioDAO($conexion);
        $resultado = $dao->registrarUsuario($cedula, $nombre, $apellido, $claveHash, $rol);
        unset($dao, $conexion);
        Conexion::desconectar();

        if (!$resultado) {
            RespuestaJSON::error("No se pudo registrar el empleado", 400);
        }

        RespuestaJSON::exito(["mensaje" => "Empleado ingresado exitosamente"], 201);
    }

    private function modificar(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
        }

        $cedula = trim($datos["cedula"] ?? "");
        $nombre = trim($datos["nombre"] ?? "");
        $apellido = trim($datos["apellido"] ?? "");
        $clave = $datos["clave"] ?? "";
        $rol = trim($datos["rol"] ?? "");

        if ($cedula === "" || $nombre === "" || $apellido === "" || $clave === "" || $rol === "") {
            RespuestaJSON::error("Existen campos vacíos", 422);
        }

        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
            RespuestaJSON::error("Cédula incorrecta", 422);
        }

        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $conexion = Conexion::conectar();
        $dao = new UsuarioDAO($conexion);
        $resultado = $dao->modificarUsuario($cedula, $nombre, $apellido, $claveHash, $rol);
        unset($dao, $conexion);
        Conexion::desconectar();

        if (!$resultado) {
            RespuestaJSON::error("No se pudo modificar el empleado", 400);
        }

        RespuestaJSON::exito(["mensaje" => "Empleado modificado exitosamente"]);
    }

    private function baja(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
        }
        
        $cedula = trim($datos["cedula"] ?? "");

        if ($cedula === "") {
            RespuestaJSON::error("Falta la cédula del empleado", 422);
        }

        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
            RespuestaJSON::error("Cédula incorrecta", 422);
        }

        $conexion = Conexion::conectar();
        $dao = new UsuarioDAO($conexion);
        $resultado = $dao->eliminarUsuario($cedula);
        unset($dao, $conexion);
        Conexion::desconectar();

        if (!$resultado) {
            RespuestaJSON::error("No se pudo eliminar el empleado", 400);
        }

        RespuestaJSON::exito(["mensaje" => "Empleado eliminado exitosamente"]);
    }

}