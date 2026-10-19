<?php

// Sigue el mismo patrón que UsuarioController:
//   - un solo archivo recibe el pedido (GET para ver, POST para crear),
//   - comprueba sesión, rol y token,
//   - y contesta en JSON (con RespuestaJson).

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/TicketDAO.php";
require_once __DIR__ . "/../vista/RespuestaJSON.php";

class TicketController
{
    private const TIPOS_DE_SALON = ["Taller", "Laboratorio"];
    private const TURNOS = ["M", "V", "N"];
    private const URGENCIAS = ["Nula", "Poca", "Media", "Alta"];
    
    public function gestionar(string $metodo): void
    {
        if (!isset($_SESSION["cedula"])) {
            RespuestaJson::error("Acceso denegado: sesión no iniciada", 401);
        }
        
        //https://www.w3schools.com/php/php_match.asp
        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->alta(),
            default => RespuestaJson::error("Método no permitido", 405),
        };
    }

    // Ver los tickets: solo el técnico
    private function listar(): void
    {
        if (!($_SESSION["tecnico"] ?? false)) {
            RespuestaJson::error("Acceso denegado: rol incorrecto", 403);
        }

        $conexion = $this->conectar();
        $dao = new TicketDAO($conexion);
        RespuestaJson::exito($dao->listarTickets());
    }

    // Crear un ticket: solo el docente
    private function alta(): void
    {
        if (!($_SESSION["docente"] ?? false)) {
            RespuestaJson::error("Acceso denegado: rol incorrecto", 403);
        }

        $this->verificarCsrf();

        $datos = json_decode(file_get_contents("php://input"), true) ?? [];

        $estudianteACargo = trim($datos["estudiante_a_cargo"] ?? "");
        $horaDeEntrada = trim($datos["hora_de_entrada"] ?? "");
        $horaDeSalida = trim($datos["hora_de_salida"] ?? "");
        $tipoDeSalon = trim($datos["tipo_de_salon"] ?? "");
        $numeroDeSalon = trim($datos["numero_de_salon"] ?? "");
        $numeroDeEquipo = trim($datos["numero_de_equipo"] ?? "");
        $asignatura = trim($datos["asignatura"] ?? "");
        $grupo = trim($datos["grupo"] ?? "");
        $turno = trim($datos["turno"] ?? "");
        $urgencia = trim($datos["urgencia"] ?? "");
        $estado = trim($datos["estado"] ?? "");

        if (
            $estudianteACargo === "" || $horaDeEntrada === "" || $horaDeSalida === "" || 
            $tipoDeSalon === "" || $numeroDeSalon === "" || $numeroDeEquipo === "" || 
            $asignatura === "" || $grupo === "" || $turno === "" || $urgencia === "" || 
            $estado === ""
        ) {
            RespuestaJson::error("Existen campos vacíos", 422);
        }

        if (!in_array($tipoDeSalon, self::TIPOS_DE_SALON)) {
            RespuestaJson::error("Tipo de salón inválido", 422);
        }

        if (!in_array($turno, self::TURNOS)) {
            RespuestaJson::error("Turno inválido", 422);
        }

        if (!in_array($urgencia, self::URGENCIAS)) {
            RespuestaJson::error("Urgencia inválida", 422);
        }

        $numeroDeSalon = filter_var($numeroDeSalon, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $numeroDeEquipo = filter_var($numeroDeEquipo, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($numeroDeSalon === false || $numeroDeEquipo === false) {
            RespuestaJson::error("El número de salón y el de equipo deben ser números positivos", 422);
        }

        $formatoHora = "/^(?:[01]\d|2[0-3]):[0-5]\d$/"; // Formato HH:MM
        if (!preg_match($formatoHora, $horaDeEntrada) || !preg_match($formatoHora, $horaDeSalida)) {
            RespuestaJson::error("Hora incorrecta", 422);
        }

        if ($horaDeSalida < $horaDeEntrada) {
            RespuestaJson::error("La hora de salida no puede ser anterior a la de entrada", 422);
        }

        $conexion = $this->conectar();
        $dao = new TicketDAO($conexion);

        if (!($dao->existeSalon($tipoDeSalon, $numeroDeSalon))) {
            RespuestaJson::error("El salón seleccionado no existe", 422);
        }

        $resultado = $dao->registrarTickets(
            $_SESSION["cedula"],
            $estudianteACargo,
            $horaDeEntrada,
            $horaDeSalida,
            $tipoDeSalon,
            $numeroDeSalon,
            $numeroDeEquipo,
            $asignatura,
            $grupo,
            $turno,
            $urgencia,
            $estado
        );

        if (!$resultado) {
            RespuestaJson::error("No se pudo registrar el ticket", 400);
        }

        RespuestaJson::exito(["mensaje" => "Ticket registrado correctamente"], 201);
    }

    private function verificarCsrf (): void
    {
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
        if (!isset($_SESSION["csrfToken"]) || !hash_equals($_SESSION["csrfToken"], $token)) {
            RespuestaJson::error("Solicitud rechazada", 403);
        }
    }

    private function conectar(): PDO
    {
        $conector = new ConectorPDO($_ENV["DB_HOST"], $_ENV["DB_USUARIO"], $_ENV["DB_CLAVE"], $_ENV["DB_NOMBRE"]);
        $conexion = $conector->establecerConexion();
        if ($conexion === null) {
            RespuestaJson::error("Error de conexión con la base de datos", 500);
        }
        return $conexion;
    }
}