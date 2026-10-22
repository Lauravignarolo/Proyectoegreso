
const API_INVENTARIO = "../index.php?ruta=inventario";

const tablaInventario = document.getElementById("TbodyInventario");
const mensajeInventario = document.getElementById("mensajeInventario");

async function cargarInventario() {
    tablaInventario.replaceChildren();

    if (mensajeInventario) {
        mensajeInventario.textContent = "Cargando inventario...";
    }

    try {
        const respuesta = await fetch(API_INVENTARIO, {
            method: "GET",
            credentials: "same-origin"
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudo cargar el inventario."
            );
        }

        if (!Array.isArray(json.datos)) {
            throw new Error("La API no devolvió una lista válida.");
        }

        if (mensajeInventario) {
            mensajeInventario.textContent =
                json.datos.length === 0
                    ? "No hay componentes registrados."
                    : "";
        }

        json.datos.forEach((item) => {
            const fila = document.createElement("tr");

            const columnas = [
                item.componente,
                item.marca,
                item.cantidad
            ];

            columnas.forEach((valor) => {
                const celda = document.createElement("td");
                celda.textContent = valor ?? "";
                fila.appendChild(celda);
            });

            tablaInventario.appendChild(fila);
        });

    } catch (error) {
        console.error("Error al cargar inventario:", error);

        if (mensajeInventario) {
            mensajeInventario.textContent = error.message;
        }
    }
}

document.addEventListener("DOMContentLoaded", cargarInventario);
