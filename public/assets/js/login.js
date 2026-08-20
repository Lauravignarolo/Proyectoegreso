// Tomar los elementos para cambiar el icono y mostrar la contraseña

const password = document.getElementById("clave");
const icono = document.getElementById("ojo");

// Cambiar el tipo de input al presionar el icono

icono.addEventListener("click", () => {

    if (password.type === "password") {

        password.type = "text";

        icono.classList.remove("bi-eye-slash");
        icono.classList.add("bi-eye");

    } else {

        password.type = "password";

        icono.classList.remove("bi-eye");
        icono.classList.add("bi-eye-slash");

    }

});