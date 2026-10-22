/**
 * Revisa que todos los controles de formulario tengan atributo `name`.
 *
 * Sin `name`, el control no viaja en FormData, así que el backend nunca
 * lo recibe y la validación falla (o se guarda un valor vacío).
 */

const fs = require("fs");
const path = require("path");

const raiz = path.resolve(__dirname, "..");
const paginasDir = path.join(raiz, "public", "paginas");

let errores = 0;

for (const pagina of fs.readdirSync(paginasDir).filter((f) => f.endsWith(".html"))) {
    const html = fs.readFileSync(path.join(paginasDir, pagina), "utf8");

    const controles = [
        ...html.matchAll(/<(input|select|textarea)\b([^>]*)>/gi)
    ].map((m) => ({ tipo: m[1].toLowerCase(), attrs: m[2] }));

    const sinNombre = controles.filter(
        (c) =>
            !/\bname\s*=/.test(c.attrs) &&
            !/\btype\s*=\s*"(submit|button|reset)"/i.test(c.attrs)
    );

    if (sinNombre.length > 0) {
        console.log(`\n${pagina}:`);

        for (const c of sinNombre) {
            const id = (c.attrs.match(/\bid\s*=\s*"([^"]*)"/) || [])[1] ?? "?";
            console.log(`   [X] <${c.tipo} id="${id}"> sin atributo name`);
            errores++;
        }
    }
}

console.log(
    errores === 0
        ? "\nTodos los controles tienen name."
        : `\n${errores} control(es) sin name.`
);

process.exit(errores === 0 ? 0 : 1);
