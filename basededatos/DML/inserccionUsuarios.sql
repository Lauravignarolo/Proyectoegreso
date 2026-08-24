/*
    Inserciones iniciales para el testeo del sistema.
    Contraseña de todos los usuarios: clave1234567
*/

INSERT INTO USUARIO (documento_identidad, contrasena, nombre, apellido)
VALUES ('11111111', '$2y$12$ki0bVkt8cnZuR4v6aJvhhelaeQc1/4fec2txUcuG1Ybr4cvnhg2sS', 'Juan', 'Pérez');

INSERT INTO USUARIO (documento_identidad, contrasena, nombre, apellido)
VALUES ('22222222', '$2y$12$ki0bVkt8cnZuR4v6aJvhhelaeQc1/4fec2txUcuG1Ybr4cvnhg2sS', 'Ana', 'Gómez');

INSERT INTO USUARIO (documento_identidad, contrasena, nombre, apellido)
VALUES ('33333333', '$2y$12$ki0bVkt8cnZuR4v6aJvhhelaeQc1/4fec2txUcuG1Ybr4cvnhg2sS', 'Carlos', 'Rodríguez');

INSERT INTO USUARIO (documento_identidad, contrasena, nombre, apellido)
VALUES ('44444444', '$2y$12$ki0bVkt8cnZuR4v6aJvhhelaeQc1/4fec2txUcuG1Ybr4cvnhg2sS', 'María', 'López');


INSERT INTO DOCENTE (documento_identidad)
VALUES ('11111111');

INSERT INTO ADMINISTRADOR (documento_identidad)
VALUES ('22222222');

INSERT INTO TECNICO (documento_identidad)
VALUES ('33333333');

INSERT INTO DIRECCION (documento_identidad)
VALUES ('44444444');

