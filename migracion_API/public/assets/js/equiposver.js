
const API_EQUIPOS = "../index.php?ruta=equipos";

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
        const respuesta = await fetch(API_EQUIPOS, {
            method: "GET",
            credentials: "same-origin"
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudieron cargar los equipos."
            );
        }

        if (!Array.isArray(json.datos)) {
            throw new Error(
                "La API no devolvió una lista válida de equipos."
            );
        }

        mostrarEquipos(json.datos);

    } catch (error) {
        console.error("Error al cargar equipos:", error);

        mostrarMensaje(error.message);
    }
}

document.addEventListener("DOMContentLoaded", cargarEquipos);
