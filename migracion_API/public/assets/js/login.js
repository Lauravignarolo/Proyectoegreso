console.log("El archivo login.js se cargó correctamente");

const API_LOGIN = "../index.php?ruta=login";

const formularioLogin = document.getElementById("formularioLogin");

const entradaCedula = document.getElementById("cedula");
const entradaClave = document.getElementById("clave");

async function loguear(cedula, clave) {

    const credenciales = {
        cedula: cedula,
        clave: clave
    };

    const respuesta = await fetch(API_LOGIN, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        credentials: "same-origin",
        body: JSON.stringify(credenciales)
    });

    const json = await respuesta.json();

    if (!respuesta.ok) {
        throw new Error(
            json.mensaje ?? "No se pudo iniciar sesión."
        );
    }

    
sessionStorage.setItem("csrfToken", json.csrfToken);

    return json.datos;
}

async function gestionarLogin(eventoFormulario) {

    eventoFormulario.preventDefault();

    try {

        const sesion = await loguear(
            entradaCedula.value.trim(),
            entradaClave.value
        );

        if (!sesion.csrfToken) {
            throw new Error("La API no devolvió el token CSRF.");
        }

        sessionStorage.setItem("csrfToken", sesion.csrfToken);

        // Redirección según el rol del usuario
        switch (sesion.rol) {

            case "administrador":
                window.location.replace("./Administrador.html");
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
