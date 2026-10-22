
const formularioLogin = document.getElementById("formularioLogin");

const entradaCedula = document.getElementById("cedula");
const entradaClave = document.getElementById("clave");

// Botón del ojo: alterna entre contraseña oculta y visible.
const botonOjo = document.getElementById("botonOjo");
const iconoOjo = document.getElementById("ojo");

/**
 * Pasa el campo de contraseña de "password" a "text" y viceversa,
 * cambiando también el icono (ojo tachado = oculta, ojo = visible).
 */
function alternarVisibilidadClave(evento) {
    if (evento) {
        evento.preventDefault();
    }

    const estaOculta = entradaClave.type === "password";

    entradaClave.type = estaOculta ? "text" : "password";

    if (iconoOjo) {
        // Ojo tachado mientras está oculta; ojo abierto cuando se muestra.
        iconoOjo.classList.toggle("bi-eye-slash", !estaOculta);
        iconoOjo.classList.toggle("bi-eye", estaOculta);
    }

    const proximaAccion = estaOculta
        ? "Ocultar contraseña"
        : "Mostrar contraseña";

    if (botonOjo) {
        botonOjo.title = proximaAccion;
        botonOjo.setAttribute("aria-label", proximaAccion);
    }

    // Devolver el foco al campo para poder seguir escribiendo.
    entradaClave.focus();
}

if (botonOjo) {
    botonOjo.addEventListener("click", alternarVisibilidadClave);

    // También con Enter o espacio, porque el botón se puede enfocar.
    botonOjo.addEventListener("keydown", (evento) => {
        if (evento.key === "Enter" || evento.key === " ") {
            alternarVisibilidadClave(evento);
        }
    });
}

async function loguear(cedula, clave) {

    const credenciales = {
        cedula: cedula,
        clave: clave
    };

    const datos = await peticionAPI(API_LOGIN, {
        metodo: "POST",
        datos: credenciales
    });

    return datos;
}

async function gestionarLogin(eventoFormulario) {

    eventoFormulario.preventDefault();

    try {

        const sesion = await loguear(
            entradaCedula.value.trim(),
            entradaClave.value
        );

        if (!sesion || !sesion.csrfToken) {
            throw new Error("La API no devolvió el token CSRF.");
        }

        guardarTokenCsrf(sesion.csrfToken);

        // Redirección según el rol del usuario
        switch (sesion.rol) {

            case "administrador":
                window.location.replace("./administrador.html");
                break;

            case "docente":
                window.location.replace("./docente.html");
                break;

            case "tecnico":
                window.location.replace("./tecnico.html");
                break;

            case "direccion":
                window.location.replace("./direccion.html");
                break;

            default:
                throw new Error("El usuario no tiene un rol válido.");
        }

    } catch (error) {
        window.alert(error.message);
    }
}

formularioLogin.addEventListener("submit", gestionarLogin);
