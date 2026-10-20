
const API_LOGIN = "/migracion_API/public/index.php?ruta=login";

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
        body: JSON.stringify(credenciales)
    });

    const json = await respuesta.json();

    if (!respuesta.ok) {
        throw new Error(json.mensaje ?? "No se pudo iniciar sesión.");
    }

    return json.datos;
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

        sessionStorage.setItem("csrfToken", sesion.csrfToken);

        window.alert("Sesión iniciada exitosamente.");
        window.location.replace("./administrador.html");

    } catch (error) {
        window.alert(error.message);
    }
}

if (formularioLogin && entradaCedula && entradaClave) {
    formularioLogin.addEventListener("submit", gestionarLogin);
} else {
    console.error(
        "No se encontraron el formulario o los campos del login. Revisá los ID en login.html."
    );
}
