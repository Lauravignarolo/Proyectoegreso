
// Tabla de salones
const TbodySalones = document.getElementById("TbodySalones");

// Botón para agregar salón
const ButtonAgregarSalon =
    document.getElementById("ButtonAgregarSalon");

// Botón para cerrar
const ButtonCerrarGestionarSalones =
    document.getElementById("ButtonCerrarGestionarSalones");

// Dialog
const DialogGestionarSalones =
    document.querySelector(".DialogGestionarSalones");

// Formulario
const FormGestionarSalones =
    document.getElementById("FormGestionarSalones");


// Obtener salones desde la API
async function obtenerSalones() {

    const salones = await peticionAPI(API_SALONES, {
        metodo: "GET"
    });

    if (!Array.isArray(salones)) {
        throw new Error("La API no devolvió una lista de salones.");
    }

    return salones;
}


// Mostrar salones en la tabla
function mostrarSalones(salones) {

    TbodySalones.innerHTML = "";

    if (salones.length === 0) {
        const fila = TbodySalones.insertRow();
        const celda = fila.insertCell();

        celda.colSpan = 3;
        celda.textContent = "No hay salones registrados.";
        return;
    }

    salones.forEach(salon => {

        const fila = TbodySalones.insertRow();

        const celdaTipo = fila.insertCell();
        celdaTipo.textContent = salon.tipo_de_salon;

        const celdaNumero = fila.insertCell();
        celdaNumero.textContent = salon.numero_de_salon;

        const celdaAccion = fila.insertCell();

        const botonEliminar = document.createElement("button");

        botonEliminar.type = "button";
        botonEliminar.className = "ButtonEliminar";
        botonEliminar.textContent = "Eliminar";

        botonEliminar.addEventListener("click", async () => {
            const confirmacion = confirm(
                `¿Está seguro de eliminar el salón ${salon.numero_de_salon}?`
            );

            if (!confirmacion) {
                return;
            }

            try {
                await peticionAPI(API_SALONES, {
                    metodo: "DELETE",
                    datos: {
                        tipo_de_salon: salon.tipo_de_salon,
                        numero_de_salon: salon.numero_de_salon
                    }
                });

                await cargarSalones();

            } catch (error) {
                alert(error.message);
                console.error(error);
            }
        });

        celdaAccion.appendChild(botonEliminar);
    });
}


// Cargar salones al abrir la página
async function cargarSalones() {

    if (!TbodySalones) {
        return;
    }

    try {
        const salones = await obtenerSalones();
        mostrarSalones(salones);

    } catch (error) {

        TbodySalones.innerHTML = "";

        const fila = TbodySalones.insertRow();
        const celda = fila.insertCell();

        celda.colSpan = 3;
        celda.textContent = error.message;

        console.error(error);
    }
}


// Abrir dialog
function AbrirAgregarSalon() {

    FormGestionarSalones.reset();
    DialogGestionarSalones.showModal();
}


// Cerrar dialog
function CerrarGestionarSalones() {

    FormGestionarSalones.reset();
    DialogGestionarSalones.close();
}


// Confirmación de eliminación
function confirmarEliminacionSalon(evento) {

    const confirmacion = confirm(
        "¿Está seguro de eliminar este salón?"
    );

    if (!confirmacion) {
        evento.preventDefault();
    }
}


// Antes de enviar el formulario de alta

async function gestionarSalon(evento) {

    evento.preventDefault();

    const formulario = new FormData(FormGestionarSalones);

    const datos = {
        tipo_de_salon: formulario.get("tipo_de_salon"),
        numero_de_salon: formulario.get("numero_de_salon")
    };

    if (
        !datos.tipo_de_salon ||
        !datos.numero_de_salon
    ) {
        alert("Completá todos los campos.");
        return;
    }

    const botonEnviar = FormGestionarSalones.querySelector(
        'button[type="submit"]'
    );

    if (botonEnviar) {
        botonEnviar.disabled = true;
    }

    try {

        await peticionAPI(API_SALONES, {
            metodo: "POST",
            datos: datos
        });

        alert("Salón registrado correctamente.");

        CerrarGestionarSalones();

        await cargarSalones();

    } catch (error) {

        alert(error.message);
        console.error("Error al registrar salón:", error);

    } finally {

        if (botonEnviar) {
            botonEnviar.disabled = false;
        }
    }
}



// Eventos
if (ButtonAgregarSalon && FormGestionarSalones && DialogGestionarSalones) {
    ButtonAgregarSalon.addEventListener(
        "click",
        AbrirAgregarSalon
    );
}

if (ButtonCerrarGestionarSalones && DialogGestionarSalones) {
    ButtonCerrarGestionarSalones.addEventListener(
        "click",
        CerrarGestionarSalones
    );
}

if (DialogGestionarSalones) {
    DialogGestionarSalones.addEventListener(
        "cancel",
        evento => {
            evento.preventDefault();
            CerrarGestionarSalones();
        }
    );
}

if (FormGestionarSalones) {
    FormGestionarSalones.addEventListener(
        "submit",
        gestionarSalon
    );
}


// Botones eliminar del código anterior
const FormulariosEliminarSalon =
    document.querySelectorAll(".FormularioEliminarSalon");

for (const formulario of FormulariosEliminarSalon) {
    formulario.addEventListener(
        "submit",
        confirmarEliminacionSalon
    );
}


// Cargar datos desde MySQL
cargarSalones();
