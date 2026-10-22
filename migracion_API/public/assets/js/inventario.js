
const API_INVENTARIO = "../index.php?ruta=inventario";

const ButtonAgregar = document.getElementById("ButtonAgregarComponente");
const ButtonCerrarGestionarInventario = document.getElementById("ButtonCerrarGestionarInventario");
const DialogGestionarInventario = document.querySelector(".DialogGestionarInventario");

const TbodyInventario = document.getElementById("TbodyInventario");
const FormGestionarInventario = document.getElementById("FormGestionarInventario");

const EntradaComponente = document.getElementById("Componente");
const EntradaMarca = document.getElementById("Marca");
const EntradaCantidad = document.getElementById("Cantidad");

const mensajeInventario = document.getElementById("mensajeInventario");

let InventarioEnEdicion = null;

function mostrarMensajeInventario(mensaje) {
    if (mensajeInventario) {
        mensajeInventario.textContent = mensaje;
    }
}

async function peticionInventario(metodo, datos = null) {
    const opciones = {
        method: metodo,
        credentials: "same-origin",
        headers: {}
    };

    if (datos !== null) {
        opciones.headers["Content-Type"] = "application/json";
        opciones.headers["X-CSRF-Token"] =
            sessionStorage.getItem("csrfToken") ?? "";

        opciones.body = JSON.stringify(datos);
    }

    const respuesta = await fetch(API_INVENTARIO, opciones);

    const texto = await respuesta.text();
    let json;

   try {
    json = JSON.parse(texto);
} catch {
    console.error("Estado HTTP:", respuesta.status);
    console.error("Respuesta completa de PHP:", texto);

    throw new Error(
        "Error de la API (" + respuesta.status +
        "). Abrí F12 → Consola para ver la respuesta."
    );
}

    if (!respuesta.ok) {
        throw new Error(
            json.mensaje ?? "Error al gestionar el inventario"
        );
    }

    return json.datos;
}

async function CargarTablaInventario() {
    mostrarMensajeInventario("Cargando inventario...");
    TbodyInventario.replaceChildren();

    try {
        const inventario = await peticionInventario("GET");

        if (!Array.isArray(inventario)) {
            throw new Error("La API no devolvió una lista válida.");
        }

        inventario.forEach(AgregarFilaItemInventario);

        mostrarMensajeInventario(
            inventario.length === 0
                ? "No hay componentes registrados."
                : ""
        );

    } catch (error) {
        console.error(error);
        mostrarMensajeInventario(error.message);
    }
}

function limpiarEstadoGestionarInventario() {
    InventarioEnEdicion = null;
    FormGestionarInventario.reset();

    EntradaComponente.readOnly = false;
    EntradaMarca.readOnly = false;
}

function AbrirAgregarInventario() {
    limpiarEstadoGestionarInventario();
    DialogGestionarInventario.showModal();
}

function AbrirEditarItemInventario(item) {
    InventarioEnEdicion = item.id_inventario;

    EntradaComponente.value = item.componente;
    EntradaMarca.value = item.marca;
    EntradaCantidad.value = item.cantidad;

    DialogGestionarInventario.showModal();
}

function CerrarGestionarInventario() {
    DialogGestionarInventario.close();
    limpiarEstadoGestionarInventario();
}

function AgregarFilaItemInventario(item) {
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

    const columnaAccion = document.createElement("td");

    const DivAccion = document.createElement("div");
    DivAccion.classList.add("DivAccion");

    const botonEditar = document.createElement("button");
    botonEditar.type = "button";
    botonEditar.classList.add("ButtonEditar");
    botonEditar.textContent = "Editar";

    botonEditar.addEventListener("click", () => {
        AbrirEditarItemInventario(item);
    });

    const botonEliminar = document.createElement("button");
    botonEliminar.type = "button";
    botonEliminar.classList.add("ButtonEliminar");
    botonEliminar.textContent = "Eliminar";

    botonEliminar.addEventListener("click", async () => {
        await EliminarItemInventario(item, botonEliminar);
    });

    DivAccion.appendChild(botonEditar);
    DivAccion.appendChild(botonEliminar);

    columnaAccion.appendChild(DivAccion);
    fila.appendChild(columnaAccion);

    TbodyInventario.appendChild(fila);
}

async function IngresarItemInventario(evento) {
    evento.preventDefault();

    const datos = {
        componente: EntradaComponente.value.trim(),
        marca: EntradaMarca.value.trim(),
        cantidad: Number(EntradaCantidad.value)
    };

    if (
        datos.componente === "" ||
        datos.marca === "" ||
        !Number.isInteger(datos.cantidad) ||
        datos.cantidad < 0
    ) {
        alert("Ingresá datos válidos.");
        return;
    }

    const botonAceptar = FormGestionarInventario.querySelector(
        'button[type="submit"]'
    );

    botonAceptar.disabled = true;

    try {
        if (InventarioEnEdicion === null) {
            await peticionInventario("POST", datos);
        } else {
            datos.id_inventario = InventarioEnEdicion;
            await peticionInventario("PUT", datos);
        }

        CerrarGestionarInventario();
        await CargarTablaInventario();

    } catch (error) {
        console.error(error);
        alert(error.message);

    } finally {
        botonAceptar.disabled = false;
    }
}

async function EliminarItemInventario(item, boton) {
    const confirmar = confirm(
        `¿Querés eliminar ${item.componente} (${item.marca})?`
    );

    if (!confirmar) {
        return;
    }

    boton.disabled = true;

    try {
        await peticionInventario("DELETE", {
            id_inventario: item.id_inventario
        });

        await CargarTablaInventario();

    } catch (error) {
        console.error(error);
        alert(error.message);
        boton.disabled = false;
    }
}

FormGestionarInventario.addEventListener(
    "submit",
    IngresarItemInventario
);

ButtonAgregar.addEventListener(
    "click",
    AbrirAgregarInventario
);

ButtonCerrarGestionarInventario.addEventListener(
    "click",
    CerrarGestionarInventario
);

DialogGestionarInventario.addEventListener(
    "cancel",
    limpiarEstadoGestionarInventario
);

document.addEventListener(
    "DOMContentLoaded",
    CargarTablaInventario
);
