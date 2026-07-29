
//Tomar datos para cambiar el icono de mostrar la contraseña
const password = document.getElementById("password");
const icono = document.getElementById("ojo");

//Cambiar el tipo de input al presionar el icono
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

//Funcion para iniciar secion con cedulas predeterminadas 
/*
function iniciarSesion() {

    const usuario = document.getElementById("cedula").value;
    const password = document.getElementById("password").value;

    if (password.trim() === "") {
        alert("Debe ingresar una contraseña");
        return;
    }

    if (usuario === "11111111") {
        window.location.href = "docente.html";
    } else if (usuario === "22222222") {
        window.location.href = "administrador.html";
    } else if (usuario === "33333333") {
        window.location.href = "tecnico.html";
    } else if (usuario === "44444444") {
        window.location.href = "direccion.html";
    } else {
        alert("Usuario no encontrado");
    }
    
}
    */