
const tablaSolicitudes = document.getElementById("tablaSolicitudes");
const mensajeSolicitudes = document.getElementById("mensajeSolicitudes");

async function cargarSolicitudes() {
    if (!tablaSolicitudes) {
        console.error("No se encontró la tabla de solicitudes");
        return;
    }

    if (mensajeSolicitudes) {
        mensajeSolicitudes.textContent = "Cargando solicitudes...";
    }

    try {
        const respuesta = await fetch(
            "../index.php?ruta=solicitudes",
            {
                method: "GET",
                credentials: "same-origin"
            }
        );

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudieron cargar las solicitudes"
            );
        }

        const solicitudes = Array.isArray(json.datos)
            ? json.datos
            : [];

        mostrarSolicitudes(solicitudes);

    } catch (error) {
        console.error("Error al cargar solicitudes:", error);

        tablaSolicitudes.replaceChildren();

        if (mensajeSolicitudes) {
            mensajeSolicitudes.textContent = error.message;
        }
    }
}

function mostrarSolicitudes(solicitudes) {
    tablaSolicitudes.replaceChildren();

    if (solicitudes.length === 0) {
        if (mensajeSolicitudes) {
            mensajeSolicitudes.textContent =
                "No hay solicitudes registradas.";
        }
        return;
    }

    if (mensajeSolicitudes) {
        mensajeSolicitudes.textContent = "";
    }

    solicitudes.forEach((solicitud) => {
        const fila = document.createElement("tr");

        const datos = [
            solicitud.id_solicitud,
            solicitud.descripcion,
            `${solicitud.nombre ?? ""} ${solicitud.apellido ?? ""}`.trim(),
            solicitud.fecha_solicitada
        ];

        datos.forEach((dato) => {
            const celda = document.createElement("td");
            celda.textContent = dato ?? "";
            fila.appendChild(celda);
        });

        tablaSolicitudes.appendChild(fila);
    });
}

document.addEventListener("DOMContentLoaded", cargarSolicitudes);
