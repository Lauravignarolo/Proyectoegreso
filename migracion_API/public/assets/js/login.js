
const formularioLogin = document.getElementById("formularioLogin");

const entradaCedula = document.getElementById("cedula");
const entradaClave = document.getElementById("clave");

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
