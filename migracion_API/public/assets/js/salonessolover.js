
const API_SALONES_SOLO_VER = "../index.php?ruta=salones";

const tablaSalonesSoloVer =
    document.getElementById("TbodySalones");

const mensajeSalonesSoloVer =
    document.getElementById("mensajeSalones");

async function cargarSalonesSoloVer() {

    if (!tablaSalonesSoloVer) {
        console.error("No se encontró la tabla de salones.");
        return;
    }

    mensajeSalonesSoloVer.textContent = "Cargando salones...";

    try {
        const respuesta = await fetch(
            API_SALONES_SOLO_VER,
            {
                method: "GET",
                credentials: "same-origin"
            }
        );

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudieron cargar los salones."
            );
        }

        if (!Array.isArray(json.datos)) {
            throw new Error(
                "La API no devolvió una lista válida de salones."
            );
        }

        mostrarSalonesSoloVer(json.datos);

    } catch (error) {
        console.error("Error al cargar salones:", error);

        tablaSalonesSoloVer.replaceChildren();

        mensajeSalonesSoloVer.textContent = error.message;
    }
}

function mostrarSalonesSoloVer(salones) {

    tablaSalonesSoloVer.replaceChildren();

    if (salones.length === 0) {
        mensajeSalonesSoloVer.textContent =
            "No hay salones registrados.";
        return;
    }

    mensajeSalonesSoloVer.textContent = "";

    salones.forEach((salon) => {

        const fila = document.createElement("tr");

        const celdaTipo = document.createElement("td");
        celdaTipo.textContent = salon.tipo_de_salon ?? "";

        const celdaNumero = document.createElement("td");
        celdaNumero.textContent = salon.numero_de_salon ?? "";

        fila.appendChild(celdaTipo);
        fila.appendChild(celdaNumero);

        tablaSalonesSoloVer.appendChild(fila);
    });
}

document.addEventListener(
    "DOMContentLoaded",
    cargarSalonesSoloVer
);
