
const tablaEquipos = document.getElementById("TbodyEquipos");
const mensajeEquipos = document.getElementById("mensajeEquipos");

function mostrarMensaje(texto) {
    mensajeEquipos.textContent = texto;
}

function agregarCelda(fila, valor) {
    const celda = document.createElement("td");

    celda.textContent = valor ?? "";

    fila.appendChild(celda);
}

function mostrarEquipos(equipos) {
    tablaEquipos.replaceChildren();

    if (equipos.length === 0) {
        mostrarMensaje("No hay computadoras registradas.");
        return;
    }

    mostrarMensaje("");

    equipos.forEach((equipo) => {
        const fila = document.createElement("tr");

        agregarCelda(fila, equipo.numero_de_equipo);
        agregarCelda(fila, equipo.tipo_de_salon);
        agregarCelda(fila, equipo.numero_de_salon);

        tablaEquipos.appendChild(fila);
    });
}

async function cargarEquipos() {
    tablaEquipos.replaceChildren();

    mostrarMensaje("Cargando equipos...");

    try {
        const equipos = await peticionAPI(API_EQUIPOS, {
            metodo: "GET"
        });

        if (!Array.isArray(equipos)) {
            throw new Error(
                "La API no devolvió una lista válida de equipos."
            );
        }

        mostrarEquipos(equipos);

    } catch (error) {
        console.error("Error al cargar equipos:", error);

        mostrarMensaje(error.message);
    }
}

document.addEventListener("DOMContentLoaded", cargarEquipos);
