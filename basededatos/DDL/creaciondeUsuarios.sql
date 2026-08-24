CREATE TABLE USUARIO ( 
    documento_identidad CHAR(8) NOT NULL, 
    contrasena VARCHAR(255) NOT NULL, 
    nombre VARCHAR(50) NOT NULL, 
    apellido VARCHAR(50) NOT NULL, 
 
    CONSTRAINT pk_usuario 
        PRIMARY KEY (documento_identidad) 
); 
 
 
CREATE TABLE ADMINISTRADOR ( 
    documento_identidad CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_administrador 
        PRIMARY KEY (documento_identidad) 
); 
 
 
CREATE TABLE TECNICO ( 
    documento_identidad CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_tecnico 
        PRIMARY KEY (documento_identidad) 
); 
 
 
CREATE TABLE DOCENTE ( 
    documento_identidad CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_docente 
        PRIMARY KEY (documento_identidad) 
); 
 
 
CREATE TABLE DIRECCION ( 
    documento_identidad CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_direccion 
        PRIMARY KEY (documento_identidad) 
); 


CREATE TABLE SALON (
    numero_de_salon INT NOT NULL,
    tipo_de_salon VARCHAR(50) NOT NULL,

    CONSTRAINT pk_salon
        PRIMARY KEY (numero_de_salon, tipo_de_salon)
);


CREATE TABLE TICKETS ( 
    id_ticket INT NOT NULL, 
    numero_de_equipo INT NOT NULL,
    numero_de_salon INT NOT NULL,
    tipo_de_salon VARCHAR(50) NOT NULL,
    asignatura VARCHAR(100) NOT NULL, 
    hora_de_entrada TIME NOT NULL, 
    hora_de_salida TIME, 
    grupo VARCHAR(50) NOT NULL, 
    turno VARCHAR(50) NOT NULL, 
 
    CONSTRAINT pk_tickets 
        PRIMARY KEY (id_ticket) 
); 


CREATE TABLE AVISO_DE_ESTADO ( 
    id_ticket INT NOT NULL, 
    urgencia VARCHAR(20) NOT NULL, 
    numero_de_equipo INT NOT NULL,
    estudiante_a_cargo CHAR(8) NOT NULL, 
    estado VARCHAR(50) NOT NULL, 
 
    CONSTRAINT pk_aviso_estado 
        PRIMARY KEY (id_ticket) 
); 


CREATE TABLE REGISTRO_DIARIO ( 
    id_ticket INT NOT NULL, 
 
    CONSTRAINT pk_registro_diario 
        PRIMARY KEY (id_ticket) 
); 


CREATE TABLE SOLICITUD ( 
    id_solicitud INT NOT NULL, 
    fecha_solicitada DATE NOT NULL, 
    descripcion VARCHAR(255) NOT NULL, 
 
    CONSTRAINT pk_solicitud 
        PRIMARY KEY (id_solicitud) 
); 


CREATE TABLE SOLUCIONA ( 
    id_ticket INT NOT NULL, 
    documento_identidad CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_soluciona 
        PRIMARY KEY (id_ticket, documento_identidad) 
); 


CREATE TABLE PIDE ( 
    id_solicitud INT NOT NULL, 
    documento_identidad CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_pide 
        PRIMARY KEY (id_solicitud, documento_identidad) 
); 


CREATE TABLE HACE ( 
    documento_identidad CHAR(8) NOT NULL, 
    id_ticket INT NOT NULL, 
 
    CONSTRAINT pk_hace 
        PRIMARY KEY (documento_identidad, id_ticket) 
); 


CREATE TABLE CREA ( 
    administrador CHAR(8) NOT NULL, 
    usuario CHAR(8) NOT NULL, 
 
    CONSTRAINT pk_crea 
        PRIMARY KEY (administrador, usuario) 
); 


ALTER TABLE ADMINISTRADOR 
    ADD CONSTRAINT fk_administrador_usuario 
    FOREIGN KEY (documento_identidad) 
    REFERENCES USUARIO (documento_identidad); 


ALTER TABLE DOCENTE 
    ADD CONSTRAINT fk_docente_usuario 
    FOREIGN KEY (documento_identidad) 
    REFERENCES USUARIO (documento_identidad); 


ALTER TABLE TECNICO 
    ADD CONSTRAINT fk_tecnico_usuario 
    FOREIGN KEY (documento_identidad) 
    REFERENCES USUARIO (documento_identidad); 


ALTER TABLE DIRECCION 
    ADD CONSTRAINT fk_direccion_usuario 
    FOREIGN KEY (documento_identidad) 
    REFERENCES USUARIO (documento_identidad); 


ALTER TABLE TICKETS
    ADD CONSTRAINT fk_tickets_salon
    FOREIGN KEY (numero_de_salon, tipo_de_salon)
    REFERENCES SALON (numero_de_salon, tipo_de_salon);


ALTER TABLE AVISO_DE_ESTADO 
    ADD CONSTRAINT fk_aviso_de_estado_ticket 
    FOREIGN KEY (id_ticket) 
    REFERENCES TICKETS (id_ticket); 


ALTER TABLE REGISTRO_DIARIO 
    ADD CONSTRAINT fk_registro_diario_ticket 
    FOREIGN KEY (id_ticket) 
    REFERENCES TICKETS (id_ticket); 


ALTER TABLE SOLUCIONA 
    ADD CONSTRAINT fk_soluciona_ticket 
    FOREIGN KEY (id_ticket) 
    REFERENCES TICKETS (id_ticket); 


ALTER TABLE SOLUCIONA 
    ADD CONSTRAINT fk_soluciona_tecnico 
    FOREIGN KEY (documento_identidad) 
    REFERENCES TECNICO (documento_identidad); 


ALTER TABLE PIDE 
    ADD CONSTRAINT fk_pide_solicitud 
    FOREIGN KEY (id_solicitud) 
    REFERENCES SOLICITUD (id_solicitud); 


ALTER TABLE PIDE 
    ADD CONSTRAINT fk_pide_docente 
    FOREIGN KEY (documento_identidad) 
    REFERENCES DOCENTE (documento_identidad); 


ALTER TABLE HACE 
    ADD CONSTRAINT fk_hace_docente 
    FOREIGN KEY (documento_identidad) 
    REFERENCES DOCENTE (documento_identidad); 


ALTER TABLE HACE 
    ADD CONSTRAINT fk_hace_ticket 
    FOREIGN KEY (id_ticket) 
    REFERENCES TICKETS (id_ticket); 


ALTER TABLE CREA 
    ADD CONSTRAINT fk_crea_administrador 
    FOREIGN KEY (administrador) 
    REFERENCES ADMINISTRADOR (documento_identidad); 


ALTER TABLE CREA 
    ADD CONSTRAINT fk_crea_usuario 
    FOREIGN KEY (usuario) 
    REFERENCES USUARIO (documento_identidad);