/**
 * Constantes y utilidades compartidas por el frontend.
 *
 * Este archivo se carga ANTES que el script de cada página. Concentra:
 *
 *   1. Las rutas de la API, en un solo lugar. Si mañana cambia el nombre
 *      de un recurso, se cambia acá y no en quince archivos.
 *   2. El manejo del token CSRF (lectura, guardado y borrado).
 *   3. Un helper `peticionAPI()` que hace el fetch, manda el token, parsea
 *      el JSON y convierte los errores de la API en excepciones de JS.
 *
 * Todas las páginas cuelgan de `../index.php?ruta=...`, así que las rutas
 * son relativas a la carpeta `public/paginas/`.
 */

// ---------------------------------------------------------------------------
// Rutas de la API
// ---------------------------------------------------------------------------

const API_BASE = "../index.php";

const API_LOGIN = `${API_BASE}?ruta=login`;
const API_LOGOUT = `${API_BASE}?ruta=logout`;
const API_USUARIOS = `${API_BASE}?ruta=usuarios`;
const API_SALONES = `${API_BASE}?ruta=salones`;
const API_EQUIPOS = `${API_BASE}?ruta=equipos`;
const API_TICKETS = `${API_BASE}?ruta=tickets`;
const API_SOLICITUDES = `${API_BASE}?ruta=solicitudes`;
const API_REGISTRO_DIARIO = `${API_BASE}?ruta=registroDiario`;
const API_INVENTARIO = `${API_BASE}?ruta=inventario`;

// ---------------------------------------------------------------------------
// Token CSRF
// ---------------------------------------------------------------------------

const CLAVE_TOKEN_CSRF = "csrfToken";

/**
 * Devuelve el token CSRF de la sesión actual.
 */
function obtenerTokenCsrf() {
    return sessionStorage.getItem(CLAVE_TOKEN_CSRF) ?? "";
}

/**
 * Guarda el token CSRF que devuelve la API al iniciar sesión.
 */
function guardarTokenCsrf(token) {
    if (typeof token === "string" && token !== "") {
        sessionStorage.setItem(CLAVE_TOKEN_CSRF, token);
    }
}

/**
 * Borra el token CSRF (se usa al cerrar sesión).
 */
function borrarTokenCsrf() {
    sessionStorage.removeItem(CLAVE_TOKEN_CSRF);
}

// ---------------------------------------------------------------------------
// Cliente HTTP
// ---------------------------------------------------------------------------

/**
 * Hace una petición a la API y devuelve el contenido de "datos".
 *
 * @param {string} ruta              Ruta de la API (una de las API_*).
 * @param {object} [opciones]
 * @param {string} [opciones.metodo] Verbo HTTP. Por defecto "GET".
 * @param {object} [opciones.datos]  Cuerpo de la petición. Si se pasa, se
 *                                   serializa como JSON.
 * @returns {Promise<any>} El contenido de la propiedad "datos".
 *
 * @throws {Error} Si la API responde con un error o con algo que no es JSON.
 *
 * Nota sobre `credentials`: no se setea a propósito. El valor por defecto
 * de la propiedad es "same-origin", que es justo lo que necesita una API
 * que vive en el mismo origen: que Viaje la cookie de sesión. Ver
 * `docs/propiedad-credentials.md`.
 */
async function peticionAPI(ruta, opciones = {}) {
    const metodo = (opciones.metodo ?? "GET").toUpperCase();
    const datos = opciones.datos ?? null;

    const peticion = {
        method: metodo,
        headers: {}
    };

    if (datos !== null) {
        peticion.headers["Content-Type"] = "application/json";
        peticion.body = JSON.stringify(datos);
    }

    // El token solo se exige en los verbos que modifican datos.
    if (metodo !== "GET" && metodo !== "HEAD") {
        peticion.headers["X-CSRF-Token"] = obtenerTokenCsrf();
    }

    const respuesta = await fetch(ruta, peticion);

    const texto = await respuesta.text();

    let json = null;

    try {
        json = JSON.parse(texto);
    } catch {
        console.error(
            `La API respondió con un estado ${respuesta.status} y un cuerpo que no es JSON:`,
            texto
        );

        throw new Error(
            "La API no devolvió una respuesta válida. Revisá F12 → Consola."
        );
    }

    if (!respuesta.ok) {
        throw new Error(
            json.mensaje ?? `La API respondió con el estado ${respuesta.status}.`
        );
    }

    return json.datos ?? null;
}
