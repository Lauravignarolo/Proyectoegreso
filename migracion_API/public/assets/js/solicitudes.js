
const API_SOLICITUDES = "../index.php?ruta=solicitudes";

const tablaSolicitudes = document.getElementById("tablaSolicitudes");
const mensajeSolicitudes = document.getElementById("mensajeSolicitudes");

function mostrarMensaje(texto) {
    if (mensajeSolicitudes) {
        mensajeSolicitudes.textContent = texto;
    }
}

function agregarCelda(fila, valor) {
    const celda = document.createElement("td");
    celda.textContent = valor ?? "";
    fila.appendChild(celda);
}

function mostrarSolicitudes(solicitudes) {
    tablaSolicitudes.replaceChildren();

    if (solicitudes.length === 0) {
        mostrarMensaje("No hay solicitudes pendientes.");
        return;
    }

    mostrarMensaje("");

    solicitudes.forEach((solicitud) => {
        const fila = document.createElement("tr");

        const docente = [
            solicitud.nombre,
            solicitud.apellido
        ].filter(Boolean).join(" ") || "Sin registrar";

        // Columnas de la tabla
        agregarCelda(fila, solicitud.id_solicitud);
        agregarCelda(fila, solicitud.descripcion);
        agregarCelda(fila, docente);
        agregarCelda(fila, solicitud.fecha_solicitada);

        // Columna Acción
        const celdaAccion = document.createElement("td");

        const botonCumplir = document.createElement("button");
        botonCumplir.type = "button";
        botonCumplir.className = "ButtonCumplir";
        botonCumplir.textContent = "Cumplir";

        botonCumplir.addEventListener("click", async () => {
            await cumplirSolicitud(
                solicitud.id_solicitud,
                botonCumplir
            );
        });

        celdaAccion.appendChild(botonCumplir);
        fila.appendChild(celdaAccion);

        tablaSolicitudes.appendChild(fila);
    });
}

async function cargarSolicitudes() {
    tablaSolicitudes.replaceChildren();
    mostrarMensaje("Cargando solicitudes...");

    try {
        const respuesta = await fetch(API_SOLICITUDES, {
            method: "GET",
            credentials: "same-origin"
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudieron cargar las solicitudes."
            );
        }

        if (!Array.isArray(json.datos)) {
            throw new Error(
                "La API no devolvió una lista válida de solicitudes."
            );
        }

        mostrarSolicitudes(json.datos);

    } catch (error) {
        console.error("Error al cargar solicitudes:", error);
        mostrarMensaje(error.message);
    }
}

async function cumplirSolicitud(idSolicitud, boton) {
    const confirmar = confirm(
        `¿Querés marcar la solicitud ${idSolicitud} como cumplida?`
    );

    if (!confirmar) {
        return;
    }

    boton.disabled = true;
    boton.textContent = "Procesando...";

    try {
        const respuesta = await fetch(API_SOLICITUDES, {
            method: "PATCH",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token":
                    sessionStorage.getItem("csrfToken") ?? ""
            },
            body: JSON.stringify({
                id_solicitud: idSolicitud
            })
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudo cumplir la solicitud."
            );
        }

        // Quitar la solicitud de la tabla
        const fila = boton.closest("tr");

        if (fila) {
            fila.remove();
        }

        // Actualizar los datos desde MySQL
        await cargarSolicitudes();

    } catch (error) {
        console.error("Error al cumplir solicitud:", error);

        alert(error.message);

        boton.disabled = false;
        boton.textContent = "Cumplir";
    }
}

document.addEventListener(
    "DOMContentLoaded",
    cargarSolicitudes
);
