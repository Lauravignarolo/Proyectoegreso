/**
 * Verificación estática del frontend:
 *  - comprueba que cada JS tenga sintaxis válida;
 *  - comprueba que los scripts que carga una misma página no se
 *    redeclaren (p. ej. dos `const API_USUARIOS` en la misma página);
 *  - comprueba que `constantes.js` se cargue antes que el script de la
 *    página (si no, las constantes no existen cuando el script corre).
 */

const fs = require("fs");
const path = require("path");
const vm = require("vm");

const raiz = path.resolve(__dirname, "..");
const jsDir = path.join(raiz, "public", "assets", "js");
const paginasDir = path.join(raiz, "public", "paginas");

let errores = 0;

function fallar(msg) {
    console.log("  [X] " + msg);
    errores++;
}

// ---------------------------------------------------------------------------
// 1. Sintaxis de cada archivo JS
// ---------------------------------------------------------------------------

console.log("== Sintaxis de los archivos JS ==");

const archivosJs = fs.readdirSync(jsDir).filter((f) => f.endsWith(".js"));

for (const archivo of archivosJs) {
    const ruta = path.join(jsDir, archivo);
    const codigo = fs.readFileSync(ruta, "utf8");

    try {
        new vm.Script(codigo, { filename: archivo });
        console.log("  [OK] " + archivo);
    } catch (error) {
        fallar(`${archivo}: ${error.message}`);
    }
}

// ---------------------------------------------------------------------------
// 2. Scripts cargados por cada página
// ---------------------------------------------------------------------------

console.log("\n== Scripts por página ==");

const paginas = fs
    .readdirSync(paginasDir)
    .filter((f) => f.endsWith(".html"));

for (const pagina of paginas) {
    const ruta = path.join(paginasDir, pagina);
    const html = fs.readFileSync(ruta, "utf8");

    const scripts = [...html.matchAll(/<script[^>]*src="([^"]+)"/g)]
        .map((m) => m[1])
        .filter((src) => src.includes("/assets/js/"))
        .map((src) => path.basename(src));

    console.log(`\n  ${pagina} -> [${scripts.join(", ")}]`);

    if (scripts.length === 0) {
        console.log("       (sin scripts de la aplicación)");
        continue;
    }

    // constantes.js tiene que ir primero.
    if (scripts[0] !== "constantes.js") {
        fallar(
            `${pagina}: el primer script no es constantes.js (es "${scripts[0]}"), ` +
            "las rutas de la API no van a estar definidas."
        );
    }

    // Ningún script puede cargar un archivo que ya no existe.
    for (const script of scripts) {
        if (!archivosJs.includes(script)) {
            fallar(`${pagina}: carga "${script}", que no existe.`);
        }
    }

    // Se compila todo junto: detecta redeclaraciones (const/let/function).
    const codigo = scripts
        .map((script) => fs.readFileSync(path.join(jsDir, script), "utf8"))
        .join("\n;\n");

    try {
        new vm.Script(codigo, { filename: pagina });
        console.log("       compila junto: OK");
    } catch (error) {
        fallar(`${pagina}: ${error.message}`);
    }
}

// ---------------------------------------------------------------------------
// 3. Referencias a constantes inexistentes
// ---------------------------------------------------------------------------

console.log("\n== Constantes de API referenciadas pero no definidas ==");

const definidas = new Set(
    [...fs
        .readFileSync(path.join(jsDir, "constantes.js"), "utf8")
        .matchAll(/^const\s+(API_\w+)/gm)].map((m) => m[1])
);

for (const archivo of archivosJs) {
    const codigo = fs.readFileSync(path.join(jsDir, archivo), "utf8");

    const usadas = new Set(
        [...codigo.matchAll(/\bAPI_[A-Z_]+\b/g)].map((m) => m[0])
    );

    for (const usada of usadas) {
        if (!definidas.has(usada)) {
            fallar(`${archivo}: usa "${usada}", que no está definida en constantes.js`);
        }
    }
}

// ---------------------------------------------------------------------------
// 4. Llamadas a funciones que no existen en la página
// ---------------------------------------------------------------------------

console.log("\n== Funciones llamadas pero no declaradas ==");

/**
 * Quita los comentarios antes de escanear, para que un texto tipo
 * "el token CSRF (" o "las constantes de la API (" no se lea como llamada.
 */
function sinComentarios(codigo) {
    return codigo
        .replace(/\/\*[\s\S]*?\*\//g, "")
        .replace(/(^|[^:])\/\/.*$/gm, "$1");
}

// Funciones y globales del navegador que no hace falta declarar.
const GLOBALES = new Set([
    "Array", "Blob", "Boolean", "Date", "Error", "Event", "FormData",
    "Function", "JSON", "Map", "Math", "Number", "Object", "Promise",
    "RegExp", "Set", "String", "Symbol", "URL", "URLSearchParams",
    "alert", "atob", "btoa", "clearInterval", "clearTimeout",
    "confirm", "console", "crypto", "decodeURIComponent", "document",
    "encodeURIComponent", "fetch", "getComputedStyle", "history",
    "isFinite", "isNaN", "localStorage", "location", "navigator",
    "parseFloat", "parseInt", "prompt", "queueMicrotask", "requestAnimationFrame",
    "sessionStorage", "setInterval", "setTimeout", "structuredClone", "window"
]);

// Palabras que pueden ir seguidas de paréntesis y no son llamadas.
const PALABRAS = new Set([
    "async", "await", "catch", "do", "else", "finally", "for", "function",
    "if", "in", "instanceof", "new", "of", "return", "switch", "throw",
    "try", "typeof", "void", "while", "with", "yield", "delete"
]);

for (const pagina of paginas) {
    const html = fs.readFileSync(path.join(paginasDir, pagina), "utf8");

    const scripts = [...html.matchAll(/<script[^>]*src="([^"]+)"/g)]
        .map((m) => m[1])
        .filter((src) => src.includes("/assets/js/"))
        .map((src) => path.basename(src));

    if (scripts.length === 0) {
        continue;
    }

    const codigoPagina = scripts
        .map((script) => fs.readFileSync(path.join(jsDir, script), "utf8"))
        .join("\n;\n");

    const codigo = sinComentarios(codigoPagina);

    // Todo lo que la página declara en el ámbito global.
    const declarados = new Set();

    for (const m of codigo.matchAll(
        /\b(?:function|class)\s+([A-Za-z_$][\w$]*)/g
    )) {
        declarados.add(m[1]);
    }

    for (const m of codigo.matchAll(
        /\b(?:const|let|var)\s+([A-Za-z_$][\w$]*)/g
    )) {
        declarados.add(m[1]);
    }

    // Parámetros y variables locales: se recolectan de forma aproximada
    // para no marcarlos como "no declarados".
    for (const m of codigo.matchAll(
        /(?:\(([^()]*)\)|([A-Za-z_$][\w$]*)\s*=>)/g
    )) {
        for (const nombre of (m[1] ?? "").split(",")) {
            const limpio = nombre.trim().split(/[=\s]/)[0];

            if (/^[A-Za-z_$][\w$]*$/.test(limpio)) {
                declarados.add(limpio);
            }
        }

        if (m[2]) {
            declarados.add(m[2]);
        }
    }

    const llamadas = new Set(
        [...codigo.matchAll(/(?<![.\w$])([A-Za-z_$][\w$]*)\s*\(/g)]
            .map((m) => m[1])
    );

    for (const llamada of llamadas) {
        if (
            !declarados.has(llamada) &&
            !GLOBALES.has(llamada) &&
            !PALABRAS.has(llamada)
        ) {
            fallar(`${pagina}: llama a "${llamada}()", que no está declarada`);
        }
    }
}

// ---------------------------------------------------------------------------
// 5. Elementos del DOM que el script busca y no existen en la página
// ---------------------------------------------------------------------------

console.log("\n== Elementos del DOM referenciados pero ausentes ==");

for (const pagina of paginas) {
    const html = fs.readFileSync(path.join(paginasDir, pagina), "utf8");

    const scripts = [...html.matchAll(/<script[^>]*src="([^"]+)"/g)]
        .map((m) => m[1])
        .filter((src) => src.includes("/assets/js/"))
        .map((src) => path.basename(src));

    if (scripts.length === 0) {
        continue;
    }

    const codigo = sinComentarios(
        scripts
            .map((script) => fs.readFileSync(path.join(jsDir, script), "utf8"))
            .join("\n;\n")
    );

    const ids = new Set(
        [...codigo.matchAll(/getElementById\(\s*["']([^"']+)["']\s*\)/g)]
            .map((m) => m[1])
    );

    for (const id of ids) {
        if (!html.includes(`id="${id}"`)) {
            fallar(`${pagina}: busca #${id}, que no existe en el HTML`);
        }
    }

    const clases = new Set(
        [...codigo.matchAll(/querySelector\(\s*["']\.([\w-]+)["']\s*\)/g)]
            .map((m) => m[1])
    );

    for (const clase of clases) {
        const patron = new RegExp(`class="[^"]*\\b${clase}\\b`);

        if (!patron.test(html)) {
            fallar(`${pagina}: busca .${clase}, que no existe en el HTML`);
        }
    }
}

console.log(
    errores === 0
        ? "\n== Sin errores =="
        : `\n== ${errores} error(es) ==`
);

process.exit(errores === 0 ? 0 : 1);
