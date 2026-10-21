
const API_USUARIOS = "../index.php?ruta=usuarios";

const tablaUsuarios = document.getElementById("tablaUsuarios");

// Obtener usuarios desde la API
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

// Obtener roles de cada usuario
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

    return roles.length > 0
        ? roles.join(", ")
        : "Sin rol";
}

// Eliminar un usuario
async function eliminarUsuario(cedula, boton) {
    const confirmar = confirm(
        `¿Estás seguro de que querés eliminar al usuario con cédula ${cedula}?\n\nEsta acción no se puede deshacer.`
    );

    if (!confirmar) {
        return;
    }

    boton.disabled = true;
    boton.textContent = "Eliminando...";

    try {
        const respuesta = await fetch(API_USUARIOS, {
            method: "DELETE",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token":
                    sessionStorage.getItem("csrfToken") ?? ""
            },
            body: JSON.stringify({
                cedula: cedula
            })
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudo eliminar el usuario"
            );
        }

        alert(
            json.datos?.mensaje ??
            "Usuario eliminado correctamente"
        );

        await cargarUsuarios();

    } catch (error) {
        alert(error.message);
        console.error("Error al eliminar usuario:", error);

    } finally {
        boton.disabled = false;
        boton.textContent = "Eliminar";
    }
}

// Mostrar usuarios en la tabla
function mostrarUsuarios(usuarios) {
    tablaUsuarios.innerHTML = "";

    if (!Array.isArray(usuarios)) {
        throw new Error(
            "La API no devolvió una lista de usuarios"
        );
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

        boton.addEventListener("click", () => {
            eliminarUsuario(usuario.cedula, boton);
        });

        accion.appendChild(boton);
    });
}

// Cargar usuarios al abrir la página
cargarUsuarios();
