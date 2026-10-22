
const tablaEquipos = document.getElementById("TbodyEquipos");
const mensajeEquipos = document.getElementById("mensajeEquipos");

const botonAgregar = document.getElementById("ButtonAgregarPC");
const botonCerrar = document.getElementById("ButtonCerrarGestionarEquipos");

const dialogEquipos = document.querySelector(".DialogGestionarEquipos");
const formularioEquipos = document.getElementById("FormGestionarEquipos");

const entradaPc = document.getElementById("Pc");
const selectorSalon = document.getElementById("SalonExistente");

let salonesDisponibles = [];

function mostrarMensaje(texto) {
    mensajeEquipos.textContent = texto;
}

async function cargarSalones() {
    const salones = await peticionAPI(API_SALONES, {
        metodo: "GET"
    });

    if (!Array.isArray(salones)) {
        throw new Error("La lista de salones es inválida.");
    }

    salonesDisponibles = salones;

    selectorSalon.replaceChildren();

    const opcionInicial = document.createElement("option");
    opcionInicial.value = "";
    opcionInicial.textContent = "Seleccionar salón";
    selectorSalon.appendChild(opcionInicial);

    salones.forEach((salon, indice) => {
        const opcion = document.createElement("option");

        opcion.value = String(indice);
        opcion.textContent =
            `${salon.tipo_de_salon} - Salón ${salon.numero_de_salon}`;

        selectorSalon.appendChild(opcion);
    });
}

function agregarFilaEquipo(equipo) {
    const fila = document.createElement("tr");

    const columnas = [
        equipo.numero_de_equipo,
        equipo.tipo_de_salon,
        equipo.numero_de_salon
    ];

    columnas.forEach((valor) => {
        const celda = document.createElement("td");
        celda.textContent = valor ?? "";
        fila.appendChild(celda);
    });

    const celdaAccion = document.createElement("td");

    const botonEliminar = document.createElement("button");
    botonEliminar.type = "button";
    botonEliminar.className = "ButtonEliminar";
    botonEliminar.textContent = "Eliminar";

    botonEliminar.addEventListener("click", async () => {
        await eliminarEquipo(equipo.numero_de_equipo, botonEliminar);
    });

    celdaAccion.appendChild(botonEliminar);
    fila.appendChild(celdaAccion);

    tablaEquipos.appendChild(fila);
}

async function cargarEquipos() {
    tablaEquipos.replaceChildren();
    mostrarMensaje("Cargando equipos...");

    try {
        const equipos = await peticionAPI(API_EQUIPOS, {
            metodo: "GET"
        });

        if (!Array.isArray(equipos)) {
            throw new Error("La API no devolvió una lista de equipos.");
        }

        equipos.forEach(agregarFilaEquipo);

        mostrarMensaje(
            equipos.length === 0
                ? "No hay computadoras registradas."
                : ""
        );

    } catch (error) {
        console.error(error);
        mostrarMensaje(error.message);
    }
}

async function abrirAgregarEquipo() {
    formularioEquipos.reset();
    botonAgregar.disabled = true;

    try {
        await cargarSalones();

        if (salonesDisponibles.length === 0) {
            alert(
                "No hay salones creados. El administrador debe registrar uno primero."
            );
            return;
        }

        dialogEquipos.showModal();

    } catch (error) {
        console.error(error);
        alert(error.message);

    } finally {
        botonAgregar.disabled = false;
    }
}

async function agregarEquipo(evento) {
    evento.preventDefault();

    const numeroEquipo = Number(entradaPc.value);
    const indiceSalon = Number(selectorSalon.value);

    if (
        !Number.isInteger(numeroEquipo) ||
        numeroEquipo <= 0 ||
        selectorSalon.value === "" ||
        !salonesDisponibles[indiceSalon]
    ) {
        alert("Ingresá una PC y seleccioná un salón válido.");
        return;
    }

    const salon = salonesDisponibles[indiceSalon];

    const datos = {
        numero_de_equipo: numeroEquipo,
        tipo_de_salon: salon.tipo_de_salon,
        numero_de_salon: Number(salon.numero_de_salon)
    };

    const botonAceptar = formularioEquipos.querySelector(
        'button[type="submit"]'
    );

    botonAceptar.disabled = true;

    try {
        await peticionAPI(API_EQUIPOS, {
            metodo: "POST",
            datos: datos
        });

        dialogEquipos.close();
        formularioEquipos.reset();

        await cargarEquipos();

    } catch (error) {
        console.error(error);
        alert(error.message);

    } finally {
        botonAceptar.disabled = false;
    }
}

async function eliminarEquipo(numeroEquipo, boton) {
    const confirmar = confirm(
        `¿Querés eliminar la PC ${numeroEquipo}?`
    );

    if (!confirmar) {
        return;
    }

    boton.disabled = true;

    try {
        await peticionAPI(API_EQUIPOS, {
            metodo: "DELETE",
            datos: {
                numero_de_equipo: numeroEquipo
            }
        });

        await cargarEquipos();

    } catch (error) {
        console.error(error);
        alert(error.message);
        boton.disabled = false;
    }
}

botonAgregar.addEventListener("click", abrirAgregarEquipo);

botonCerrar.addEventListener("click", () => {
    dialogEquipos.close();
});

formularioEquipos.addEventListener("submit", agregarEquipo);

document.addEventListener("DOMContentLoaded", cargarEquipos);
