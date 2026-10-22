<?php

/**
 * Prueba funcional de la API.
 *
 * Levanta el servidor embebido de PHP sobre `public/` y ejercita cada ruta
 * con los cuatro roles del sistema, comprobando el código HTTP y el
 * contenido de la respuesta.
 *
 * Uso:  php composer/probar-api.php
 */

$raiz = dirname(__DIR__);
$puerto = 8099;
$base = "http://127.0.0.1:{$puerto}/index.php";

$errores = 0;
$pruebas = 0;

/**
 * Hace una petición a la API conservando la cookie de sesión.
 */
function pedir(
    string $url,
    string $metodo = "GET",
    ?array $datos = null,
    array $cabeceras = [],
    ?string $jar = null
): array {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_HTTPHEADER => $cabeceras,
        CURLOPT_TIMEOUT => 15,
        // En Windows puede haber un proxy configurado en el sistema; el
        // servidor de pruebas es local, así que se salta siempre.
        CURLOPT_NOPROXY => "*"
    ]);

    if ($jar !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    }

    if ($datos !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    }

    $cuerpo = curl_exec($ch);

    $estado = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    if ($cuerpo === false) {
        return ["estado" => 0, "json" => null, "error" => $error];
    }

    return [
        "estado" => $estado,
        "json" => json_decode($cuerpo, true),
        "crudo" => $cuerpo
    ];
}

function comprobar(
    string $titulo,
    array $respuesta,
    int $estadoEsperado,
    bool $conToken = false
): array {
    global $errores, $pruebas;

    $pruebas++;

    $ok = $respuesta["estado"] === $estadoEsperado;

    if ($ok && $conToken) {
        $ok = isset($respuesta["json"]["datos"]["csrfToken"]);
    }

    if ($ok) {
        echo "  [OK]   {$titulo} ({$respuesta['estado']})\n";
    } else {
        $errores++;

        echo "  [X]    {$titulo}\n";
        echo "         esperado {$estadoEsperado}, recibido {$respuesta['estado']}\n";
        echo "         " . substr((string) ($respuesta["crudo"] ?? $respuesta["error"]), 0, 300) . "\n";
    }

    return $respuesta;
}

// ---------------------------------------------------------------------------
// 1. Servidor
// ---------------------------------------------------------------------------

// Si se define API_BASE se usa ese servidor y no se levanta ninguno.
// Uso:
//   php composer/probar-api.php
//   $env:API_BASE = "http://127.0.0.1:8099/index.php"; php composer/probar-api.php
$baseExterna = getenv("API_BASE");

if ($baseExterna !== false && $baseExterna !== "") {
    $base = rtrim($baseExterna, "/");

    echo "Usando servidor externo: {$base}\n\n";
} else {
    $puerta = @stream_socket_client("tcp://127.0.0.1:{$puerto}", $errno, $errstr, 1);

    if ($puerta) {
        fclose($puerta);
        fwrite(STDERR, "El puerto {$puerto} ya está ocupado.\n");
        exit(1);
    }
    $log = tempnam(sys_get_temp_dir(), "sgksi-api-");

    $descriptores = [
        // NUL: el servidor no lee de la entrada estándar.
        0 => ["file", "NUL", "r"],
        1 => ["file", $log, "a"],
        2 => ["file", $log, "a"]
    ];

    // escapeshellarg es imprescindible: PHP_BINARY puede tener espacios en
    // la ruta (por ejemplo "C:\Users\...\aca php\php.exe").
    $servidor = proc_open(
        escapeshellarg(PHP_BINARY)
            . " -S 127.0.0.1:{$puerto}"
            . " -t " . escapeshellarg($raiz . "/public"),
        $descriptores,
        $pipes,
        $raiz
    );

    if (!is_resource($servidor)) {
        fwrite(STDERR, "No se pudo levantar el servidor.\n");
        exit(1);
    }

    // Espera a que el servidor responda. Se hace una petición HTTP real
    // (no solo un TCP connect): el servidor embebido es de un solo hilo y
    // se quedaría esperando la petición de un socket abierto sin datos.
    $listo = false;

    for ($i = 0; $i < 50; $i++) {
        usleep(200000);

        $sonda = curl_init("http://127.0.0.1:{$puerto}/index.php");

        curl_setopt_array($sonda, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 1000,
            CURLOPT_NOPROXY => "*"
        ]);

        curl_exec($sonda);

        if (curl_getinfo($sonda, CURLINFO_HTTP_CODE) > 0) {
            $listo = true;
            break;
        }
    }

    if (!$listo) {
        fwrite(STDERR, "El servidor no arrancó.\n");
        proc_terminate($servidor);
        exit(1);
    }

    register_shutdown_function(function () use ($servidor, $log) {
        proc_terminate($servidor);
        proc_close($servidor);
        @unlink($log);
    });

    echo "Servidor levantado en {$base}\n\n";
}

// ---------------------------------------------------------------------------
// 2. Rutas y errores de la API
// ---------------------------------------------------------------------------

echo "== Ruteo ==\n";

comprobar("ruta inexistente -> 404", pedir($base . "?ruta=noExiste"), 404);

comprobar("sin ruta -> 400", pedir($base), 400);

comprobar("login con GET -> 405", pedir($base . "?ruta=login"), 405);

foreach ([
    "usuarios", "salones", "equipos", "tickets",
    "solicitudes", "registroDiario", "inventario"
] as $ruta) {
    comprobar(
        "{$ruta} sin sesión -> 401",
        pedir($base . "?ruta={$ruta}"),
        401
    );
}

// logout solo acepta POST: con GET el método es lo primero que se rechaza.
comprobar(
    "logout con GET -> 405",
    pedir($base . "?ruta=logout"),
    405
);

// ---------------------------------------------------------------------------
// 3. Login
// ---------------------------------------------------------------------------

echo "\n== Login ==\n";

$jarDocente = tempnam(sys_get_temp_dir(), "sgksi-jar-");
$jarAdmin = tempnam(sys_get_temp_dir(), "sgksi-jar-");
$jarTecnico = tempnam(sys_get_temp_dir(), "sgksi-jar-");
$jarDireccion = tempnam(sys_get_temp_dir(), "sgksi-jar-");

$respuesta = comprobar(
    "credenciales incorrectas -> 401",
    pedir(
        $base . "?ruta=login",
        "POST",
        ["cedula" => "11111111", "clave" => "claveIncorrecta"]
    ),
    401
);

$loginDocente = comprobar(
    "login docente -> 200",
    pedir(
        $base . "?ruta=login",
        "POST",
        ["cedula" => "11111111", "clave" => "clave1234567"],
        ["Content-Type: application/json"],
        $jarDocente
    ),
    200,
    true
);

comprobar(
    "login admin -> 200",
    pedir(
        $base . "?ruta=login",
        "POST",
        ["cedula" => "22222222", "clave" => "clave1234567"],
        ["Content-Type: application/json"],
        $jarAdmin
    ),
    200,
    true
);

$loginTecnico = comprobar(
    "login tecnico -> 200",
    pedir(
        $base . "?ruta=login",
        "POST",
        ["cedula" => "33333333", "clave" => "clave1234567"],
        ["Content-Type: application/json"],
        $jarTecnico
    ),
    200,
    true
);

comprobar(
    "login direccion -> 200",
    pedir(
        $base . "?ruta=login",
        "POST",
        ["cedula" => "44444444", "clave" => "clave1234567"],
        ["Content-Type: application/json"],
        $jarDireccion
    ),
    200,
    true
);

comprobar(
    "cédula inválida -> 422",
    pedir(
        $base . "?ruta=login",
        "POST",
        ["cedula" => "123", "clave" => "clave1234567"],
        ["Content-Type: application/json"]
    ),
    422
);

// ---------------------------------------------------------------------------
// 4. Token CSRF
// ---------------------------------------------------------------------------

echo "\n== Token CSRF ==\n";

$tokenDocente = $loginDocente["json"]["datos"]["csrfToken"];
$tokenTecnico = $loginTecnico["json"]["datos"]["csrfToken"];
$tokenAdmin = pedir(
    $base . "?ruta=login",
    "POST",
    ["cedula" => "22222222", "clave" => "clave1234567"],
    ["Content-Type: application/json"],
    $jarAdmin
)["json"]["datos"]["csrfToken"];

comprobar(
    "POST sin token -> 403",
    pedir(
        $base . "?ruta=salones",
        "POST",
        ["tipo_de_salon" => "Taller", "numero_de_salon" => 999],
        ["Content-Type: application/json"],
        $jarAdmin
    ),
    403
);

comprobar(
    "POST con token falso -> 403",
    pedir(
        $base . "?ruta=salones",
        "POST",
        ["tipo_de_salon" => "Taller", "numero_de_salon" => 999],
        ["Content-Type: application/json", "X-CSRF-Token: " . str_repeat("0", 64)],
        $jarAdmin
    ),
    403
);

// ---------------------------------------------------------------------------
// 5. Permisos por rol
// ---------------------------------------------------------------------------

echo "\n== Permisos por rol ==\n";

comprobar(
    "docente no puede listar usuarios -> 403",
    pedir($base . "?ruta=usuarios", "GET", null, [], $jarDocente),
    403
);

comprobar(
    "tecnico no puede listar usuarios -> 403",
    pedir($base . "?ruta=usuarios", "GET", null, [], $jarTecnico),
    403
);

comprobar(
    "tecnico no puede listar tickets? sí -> 200",
    pedir($base . "?ruta=tickets", "GET", null, [], $jarTecnico),
    200
);

comprobar(
    "docente no puede listar tickets -> 403",
    pedir($base . "?ruta=tickets", "GET", null, [], $jarDocente),
    403
);

comprobar(
    "admin puede listar usuarios -> 200",
    pedir($base . "?ruta=usuarios", "GET", null, [], $jarAdmin),
    200
);

comprobar(
    "cualquiera autenticado puede ver salones -> 200",
    pedir($base . "?ruta=salones", "GET", null, [], $jarDocente),
    200
);

comprobar(
    "docente no puede crear salones -> 403",
    pedir(
        $base . "?ruta=salones",
        "POST",
        ["tipo_de_salon" => "Taller", "numero_de_salon" => 777],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenDocente],
        $jarDocente
    ),
    403
);

comprobar(
    "tecnico no puede crear salones -> 403",
    pedir(
        $base . "?ruta=salones",
        "POST",
        ["tipo_de_salon" => "Taller", "numero_de_salon" => 777],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    403
);

// ---------------------------------------------------------------------------
// 6. Operaciones de escritura
// ---------------------------------------------------------------------------

echo "\n== Escrituras ==\n";

$numeroSalon = random_int(500, 900);

comprobar(
    "admin crea salón -> 201",
    pedir(
        $base . "?ruta=salones",
        "POST",
        ["tipo_de_salon" => "Taller", "numero_de_salon" => $numeroSalon],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    201
);

comprobar(
    "admin borra salón -> 200",
    pedir(
        $base . "?ruta=salones",
        "DELETE",
        ["tipo_de_salon" => "Taller", "numero_de_salon" => $numeroSalon],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    200
);

comprobar(
    "admin crea usuario -> 201",
    pedir(
        $base . "?ruta=usuarios",
        "POST",
        [
            "cedula" => "87654321",
            "nombre" => "Usuario",
            "apellido" => "DePrueba",
            "clave" => "clave1234567",
            "confirmarClave" => "clave1234567",
            "rol" => "tecnico"
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    201
);

comprobar(
    "usuario repetido -> 409",
    pedir(
        $base . "?ruta=usuarios",
        "POST",
        [
            "cedula" => "87654321",
            "nombre" => "Usuario",
            "apellido" => "DePrueba",
            "clave" => "clave1234567",
            "confirmarClave" => "clave1234567",
            "rol" => "tecnico"
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    409
);

comprobar(
    "rol inválido -> 422",
    pedir(
        $base . "?ruta=usuarios",
        "POST",
        [
            "cedula" => "87654322",
            "nombre" => "Usuario",
            "apellido" => "DePrueba",
            "clave" => "clave1234567",
            "confirmarClave" => "clave1234567",
            "rol" => "superusuario"
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    422
);

comprobar(
    "admin borra usuario -> 200",
    pedir(
        $base . "?ruta=usuarios",
        "DELETE",
        ["cedula" => "87654321"],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    200
);

comprobar(
    "admin no puede borrarse a sí mismo -> 403",
    pedir(
        $base . "?ruta=usuarios",
        "DELETE",
        ["cedula" => "22222222"],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenAdmin],
        $jarAdmin
    ),
    403
);

// Ticket: lo crea el docente, lo repara el técnico.
$salones = pedir($base . "?ruta=salones", "GET", null, [], $jarTecnico)["json"]["datos"];
$salon = $salones[0] ?? ["tipo_de_salon" => "Taller", "numero_de_salon" => 123];

comprobar(
    "docente crea ticket -> 201",
    pedir(
        $base . "?ruta=tickets",
        "POST",
        [
            "estudiante_a_cargo" => "Estudiante de Prueba",
            "hora_de_entrada" => "08:00",
            "hora_de_salida" => "09:00",
            "tipo_de_salon" => $salon["tipo_de_salon"],
            "numero_de_salon" => (int) $salon["numero_de_salon"],
            "numero_de_equipo" => 3,
            "asignatura" => "Programación",
            "grupo" => "A1",
            "turno" => "M",
            "urgencia" => "Media",
            "estado" => "No funciona"
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenDocente],
        $jarDocente
    ),
    201
);

$tickets = pedir($base . "?ruta=tickets", "GET", null, [], $jarTecnico)["json"]["datos"];

// El POST no devuelve el ID; el ticket recién creado es el de ID más alto
// entre los pendientes.
$idTicket = 0;

foreach ($tickets as $ticket) {
    $idTicket = max($idTicket, (int) $ticket["id_ticket"]);
}

comprobar(
    "tecnico repara ticket -> 200",
    pedir(
        $base . "?ruta=tickets",
        "PATCH",
        ["id_ticket" => $idTicket],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    200
);

comprobar(
    "tecnico repara dos veces -> 409",
    pedir(
        $base . "?ruta=tickets",
        "PATCH",
        ["id_ticket" => $idTicket],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    409
);

// Solicitudes.
comprobar(
    "docente crea solicitud -> 201",
    pedir(
        $base . "?ruta=solicitudes",
        "POST",
        ["fechaSolicitada" => "2026-10-22", "descripcion" => "Solicitud de prueba"],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenDocente],
        $jarDocente
    ),
    201
);

$solicitudes = pedir($base . "?ruta=solicitudes", "GET", null, [], $jarTecnico)["json"]["datos"];

$idSolicitud = 0;

foreach ($solicitudes as $solicitud) {
    $idSolicitud = max($idSolicitud, (int) $solicitud["id_solicitud"]);
}

comprobar(
    "tecnico cumple solicitud -> 200",
    pedir(
        $base . "?ruta=solicitudes",
        "PATCH",
        ["id_solicitud" => $idSolicitud],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    200
);

comprobar(
    "direccion puede ver solicitudes -> 200",
    pedir($base . "?ruta=solicitudes", "GET", null, [], $jarDireccion),
    200
);

// Registro diario.
comprobar(
    "docente crea registro diario -> 201",
    pedir(
        $base . "?ruta=registroDiario",
        "POST",
        [
            "hora_de_entrada" => "08:00",
            "hora_de_salida" => "09:00",
            "tipo_de_salon" => $salon["tipo_de_salon"],
            "numero_de_salon" => (int) $salon["numero_de_salon"],
            "asignatura" => "Programación",
            "grupo" => "A1",
            "turno" => "M"
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenDocente],
        $jarDocente
    ),
    201
);

comprobar(
    "docente ve su registro diario -> 200",
    pedir($base . "?ruta=registroDiario", "GET", null, [], $jarDocente),
    200
);

comprobar(
    "tecnico no puede ver registro diario -> 403",
    pedir($base . "?ruta=registroDiario", "GET", null, [], $jarTecnico),
    403
);

// Inventario.
comprobar(
    "admin ve inventario -> 200",
    pedir($base . "?ruta=inventario", "GET", null, [], $jarAdmin),
    200
);

comprobar(
    "docente no puede agregar al inventario -> 403",
    pedir(
        $base . "?ruta=inventario",
        "POST",
        ["componente" => "Teclado", "marca" => "Genius", "cantidad" => 5],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenDocente],
        $jarDocente
    ),
    403
);

comprobar(
    "tecnico agrega al inventario -> 201",
    pedir(
        $base . "?ruta=inventario",
        "POST",
        ["componente" => "Teclado", "marca" => "Genius", "cantidad" => 5],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    201
);

$inventario = pedir($base . "?ruta=inventario", "GET", null, [], $jarTecnico)["json"]["datos"];
$idInventario = 0;

foreach ($inventario as $item) {
    if ($item["componente"] === "Teclado" && $item["marca"] === "Genius") {
        $idInventario = max($idInventario, (int) $item["id_inventario"]);
    }
}

comprobar(
    "tecnico edita inventario -> 200",
    pedir(
        $base . "?ruta=inventario",
        "PUT",
        [
            "id_inventario" => $idInventario,
            "componente" => "Teclado",
            "marca" => "Genius",
            "cantidad" => 9
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    200
);

comprobar(
    "tecnico borra inventario -> 200",
    pedir(
        $base . "?ruta=inventario",
        "DELETE",
        ["id_inventario" => $idInventario],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    200
);

// Equipos.
comprobar(
    "tecnico crea equipo -> 201",
    pedir(
        $base . "?ruta=equipos",
        "POST",
        [
            "numero_de_equipo" => 42,
            "tipo_de_salon" => $salon["tipo_de_salon"],
            "numero_de_salon" => (int) $salon["numero_de_salon"]
        ],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    201
);

comprobar(
    "tecnico borra equipo -> 200",
    pedir(
        $base . "?ruta=equipos",
        "DELETE",
        ["numero_de_equipo" => 42],
        ["Content-Type: application/json", "X-CSRF-Token: " . $tokenTecnico],
        $jarTecnico
    ),
    200
);

// ---------------------------------------------------------------------------
// 7. Logout
// ---------------------------------------------------------------------------

echo "\n== Logout ==\n";

comprobar(
    "logout -> 200",
    pedir(
        $base . "?ruta=logout",
        "POST",
        null,
        ["X-CSRF-Token: " . $tokenDocente],
        $jarDocente
    ),
    200
);

comprobar(
    "tras logout, salones -> 401",
    pedir($base . "?ruta=salones", "GET", null, [], $jarDocente),
    401
);

comprobar(
    "logout sin sesión -> 403",
    pedir($base . "?ruta=logout", "POST"),
    403
);

// ---------------------------------------------------------------------------
// Resultado
// ---------------------------------------------------------------------------

echo "\n== Resultado ==\n";
echo "Pruebas: {$pruebas} | Errores: {$errores}\n";

exit($errores === 0 ? 0 : 1);
