
const API_USUARIOS = "../index.php?ruta=usuarios";

const tablaUsuarios = document.getElementById("tablaUsuarios");

async function cargarUsuarios() {
    try {
        const respuesta = await fetch(API_USUARIOS, {
            method: "GET",
            credentials: "same-origin"
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudieron cargar los usuarios"
            );
        }

        mostrarUsuarios(json.datos);

    } catch (error) {
        tablaUsuarios.innerHTML = "";

        const fila = tablaUsuarios.insertRow();
        const celda = fila.insertCell();

        celda.colSpan = 5;
        celda.textContent = error.message;

        console.error(error);
    }
}

function obtenerRoles(usuario) {
    const roles = [];

    if (usuario.administrador == 1) {
        roles.push("Administrador");
    }

    if (usuario.docente == 1) {
        roles.push("Docente");
    }

    if (usuario.direccion == 1) {
        roles.push("Dirección");
    }

    if (usuario.tecnico == 1) {
        roles.push("Técnico");
    }

    return roles.length > 0 ? roles.join(", ") : "Sin rol";
}

function mostrarUsuarios(usuarios) {
    tablaUsuarios.innerHTML = "";

    if (!Array.isArray(usuarios)) {
        throw new Error("La API no devolvió una lista de usuarios");
    }

    if (usuarios.length === 0) {
        const fila = tablaUsuarios.insertRow();
        const celda = fila.insertCell();

        celda.colSpan = 5;
        celda.textContent = "No hay usuarios registrados";
        return;
    }

    usuarios.forEach(usuario => {
        const fila = tablaUsuarios.insertRow();

        const cedula = fila.insertCell();
        cedula.textContent = usuario.cedula;

        const nombre = fila.insertCell();
        nombre.textContent = usuario.nombre;

        const apellido = fila.insertCell();
        apellido.textContent = usuario.apellido;

        const rol = fila.insertCell();
        rol.textContent = obtenerRoles(usuario);

        const accion = fila.insertCell();

        const boton = document.createElement("button");
        boton.type = "button";
        boton.className = "btnOperacion ButtonEliminar";
        boton.textContent = "Eliminar";
        boton.disabled = true;
        boton.title = "Función pendiente de conectar con la API";

        accion.appendChild(boton);
    });
}

cargarUsuarios();
