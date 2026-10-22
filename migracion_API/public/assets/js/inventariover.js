
const tablaInventario = document.getElementById("TbodyInventario");
const mensajeInventario = document.getElementById("mensajeInventario");

async function cargarInventario() {
    tablaInventario.replaceChildren();

    if (mensajeInventario) {
        mensajeInventario.textContent = "Cargando inventario...";
    }

    try {
        const inventario = await peticionAPI(API_INVENTARIO, {
            metodo: "GET"
        });

        if (!Array.isArray(inventario)) {
            throw new Error("La API no devolvió una lista válida.");
        }

        if (mensajeInventario) {
            mensajeInventario.textContent =
                inventario.length === 0
                    ? "No hay componentes registrados."
                    : "";
        }

        inventario.forEach((item) => {
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
