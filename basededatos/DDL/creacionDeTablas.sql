/*
    ===========================================================================
    S.G.R.S.I. - Esquema de la base de datos
    ===========================================================================

    Este es el ÚNICO script de creación de la base. Define todas las tablas,
    sus claves primarias, sus claves foráneas y sus restricciones. No hay
    scripts de migración aparte: una base creada con este archivo queda
    directamente en la versión correcta.

    Cómo usarlo
    -----------
    1. Crear la base (una sola vez):

        CREATE DATABASE proyectoegreso
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_general_ci;

    2. Cargar el esquema (desde la raíz del repositorio):

        mysql -u root -p proyectoegreso < basededatos/DDL/creacionDeTablas.sql

    3. Cargar los datos de prueba (opcional):

        mysql -u root -p proyectoegreso < basededatos/DML/inserccionUsuarios.sql

    El script no crea ni selecciona la base a propósito: así funciona igual
    con cualquier nombre de base y no pisa una base existente por accidente.

    Orden de creación
    -----------------
    Primero se crean todas las tablas (sin claves foráneas) y al final se
    agregan todas las claves foráneas. Así el archivo se lee de arriba a
    abajo sin depender del orden, y no hacen falta desactivar las
    comprobaciones de integridad.

    Todas las tablas son InnoDB con utf8mb4, que es lo que necesitan las
    transacciones y las tildes/ñ de los nombres.
    ===========================================================================
*/

SET NAMES utf8mb4;


/* ===========================================================================
   1. USUARIO Y ROLES
   ---------------------------------------------------------------------------
   USUARIO guarda los datos comunes a todas las personas del sistema. Un
   usuario tiene exactamente un rol, y el rol se guarda en su propia tabla
   (ADMINISTRADOR, DOCENTE, TECNICO o DIRECCION) que referencia a USUARIO.
   =========================================================================== */

CREATE TABLE USUARIO (
    documento_identidad CHAR(8)         NOT NULL,
    contrasena          VARCHAR(255)    NOT NULL,
    nombre              VARCHAR(50)     NOT NULL,
    apellido            VARCHAR(50)     NOT NULL,
    estado              BOOLEAN         NOT NULL DEFAULT TRUE,

    CONSTRAINT pk_usuario
        PRIMARY KEY (documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE ADMINISTRADOR (
    documento_identidad CHAR(8) NOT NULL,

    CONSTRAINT pk_administrador
        PRIMARY KEY (documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE TECNICO (
    documento_identidad CHAR(8) NOT NULL,

    CONSTRAINT pk_tecnico
        PRIMARY KEY (documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE DOCENTE (
    documento_identidad CHAR(8) NOT NULL,

    CONSTRAINT pk_docente
        PRIMARY KEY (documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE DIRECCION (
    documento_identidad CHAR(8) NOT NULL,

    CONSTRAINT pk_direccion
        PRIMARY KEY (documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   2. SALONES Y EQUIPOS
   ---------------------------------------------------------------------------
   Un salón se identifica por su número y su tipo (un laboratorio 3 y un
   taller 3 son salones distintos). Un equipo pertenece a un salón.
   =========================================================================== */

CREATE TABLE SALON (
    numero_de_salon INT         NOT NULL,
    tipo_de_salon   VARCHAR(50) NOT NULL,

    CONSTRAINT pk_salon
        PRIMARY KEY (numero_de_salon, tipo_de_salon)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE EQUIPO (
    numero_de_equipo INT         NOT NULL,
    numero_de_salon  INT         NOT NULL,
    tipo_de_salon    VARCHAR(50) NOT NULL,

    CONSTRAINT pk_equipo
        PRIMARY KEY (numero_de_equipo)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   3. TICKETS
   ---------------------------------------------------------------------------
   Un ticket es la incidencia que reporta un docente sobre un equipo. Los
   datos de la clase (asignatura, horario, grupo, turno) van en TICKETS; el
   aviso de estado (urgencia, estudiante a cargo, estado) va en
   AVISO_DE_ESTADO, que comparte la clave con el ticket.

   REGISTRO_DIARIO solo vincula un ticket con el docente que lo reportó
   (ver también HACE). No confundir con REGISTRO_DIARIO_DOCENTE, que es el
   registro de clases del docente.
   =========================================================================== */

CREATE TABLE TICKETS (
    id_ticket        INT          NOT NULL AUTO_INCREMENT,
    numero_de_equipo INT          NOT NULL,
    numero_de_salon  INT          NOT NULL,
    tipo_de_salon    VARCHAR(50)  NOT NULL,
    asignatura       VARCHAR(100) NOT NULL,
    hora_de_entrada  TIME         NOT NULL,
    hora_de_salida   TIME         NULL,
    grupo            VARCHAR(50)  NOT NULL,
    turno            VARCHAR(50)  NOT NULL,

    CONSTRAINT pk_tickets
        PRIMARY KEY (id_ticket)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE AVISO_DE_ESTADO (
    id_ticket           INT          NOT NULL,
    urgencia            VARCHAR(20)  NOT NULL,
    numero_de_equipo    INT          NOT NULL,
    estudiante_a_cargo  VARCHAR(100) NOT NULL,
    estado              VARCHAR(50)  NOT NULL,

    CONSTRAINT pk_aviso_estado
        PRIMARY KEY (id_ticket)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE REGISTRO_DIARIO (
    id_ticket INT NOT NULL,

    CONSTRAINT pk_registro_diario
        PRIMARY KEY (id_ticket)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   4. SOLICITUDES
   ---------------------------------------------------------------------------
   Un docente pide algo (SOLICITUD) y un técnico la da por cumplida
   (SOLICITUD_CUMPLIDA). La tabla PIDE registra qué docente hizo la solicitud.
   =========================================================================== */

CREATE TABLE SOLICITUD (
    id_solicitud     INT          NOT NULL AUTO_INCREMENT,
    fecha_solicitada DATE         NOT NULL,
    descripcion      VARCHAR(255) NOT NULL,

    CONSTRAINT pk_solicitud
        PRIMARY KEY (id_solicitud)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE SOLICITUD_CUMPLIDA (
    id_solicitud                  INT      NOT NULL,
    documento_identidad_tecnico   CHAR(8)  NOT NULL,
    fecha_cumplimiento             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_solicitud_cumplida
        PRIMARY KEY (id_solicitud)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   5. INVENTARIO
   ---------------------------------------------------------------------------
   Componentes sueltos (teclados, memorias, etc.) con su cantidad disponible.
   =========================================================================== */

CREATE TABLE INVENTARIO (
    id_inventario INT          NOT NULL AUTO_INCREMENT,
    componente    VARCHAR(100) NOT NULL,
    marca         VARCHAR(100) NOT NULL,
    cantidad      INT          NOT NULL DEFAULT 0,

    CONSTRAINT pk_inventario
        PRIMARY KEY (id_inventario),

    CONSTRAINT chk_inventario_cantidad
        CHECK (cantidad >= 0)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   6. REGISTRO DIARIO DEL DOCENTE
   ---------------------------------------------------------------------------
   Registro de las clases que da cada docente: horario, salón, asignatura,
   grupo y turno. Es la tabla que usa la pantalla "Registro diario".
   =========================================================================== */

CREATE TABLE REGISTRO_DIARIO_DOCENTE (
    id                  INT          NOT NULL AUTO_INCREMENT,
    documento_identidad CHAR(8)      NOT NULL,
    hora_de_entrada     TIME         NOT NULL,
    hora_de_salida      TIME         NOT NULL,
    tipo_de_salon       VARCHAR(50)  NOT NULL,
    numero_de_salon     INT          NOT NULL,
    asignatura          VARCHAR(100) NOT NULL,
    grupo               VARCHAR(50)  NOT NULL,
    turno               VARCHAR(50)  NOT NULL,
    creado_en           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_registro_diario_docente
        PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   7. TABLAS DE RELACIÓN
   ---------------------------------------------------------------------------
   SOLUCIONA  : qué técnico resolvió qué ticket.
   PIDE       : qué docente hizo qué solicitud.
   HACE       : qué docente reportó qué ticket.
   CREA       : qué administrador dio de alta qué usuario.
   =========================================================================== */

CREATE TABLE SOLUCIONA (
    id_ticket            INT     NOT NULL,
    documento_identidad  CHAR(8) NOT NULL,

    CONSTRAINT pk_soluciona
        PRIMARY KEY (id_ticket, documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE PIDE (
    id_solicitud         INT     NOT NULL,
    documento_identidad  CHAR(8) NOT NULL,

    CONSTRAINT pk_pide
        PRIMARY KEY (id_solicitud, documento_identidad)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE HACE (
    documento_identidad  CHAR(8) NOT NULL,
    id_ticket            INT     NOT NULL,

    CONSTRAINT pk_hace
        PRIMARY KEY (documento_identidad, id_ticket)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


CREATE TABLE CREA (
    administrador CHAR(8) NOT NULL,
    usuario       CHAR(8) NOT NULL,

    CONSTRAINT pk_crea
        PRIMARY KEY (administrador, usuario)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


/* ===========================================================================
   8. CLAVES FORÁNEAS
   ---------------------------------------------------------------------------
   Se agregan al final, cuando ya existen todas las tablas. Los nombres de
   las restricciones siguen el patrón fk_<tabla>_<tabla_referenciada>.
   =========================================================================== */

-- Cada rol es un usuario: no puede existir un rol sin su fila en USUARIO.
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

-- Un equipo pertenece a un salón. Si el salón cambia de número/tipo, el
-- equipo acompaña el cambio; no se puede borrar un salón con equipos.
ALTER TABLE EQUIPO
    ADD CONSTRAINT fk_equipo_salon
    FOREIGN KEY (numero_de_salon, tipo_de_salon)
    REFERENCES SALON (numero_de_salon, tipo_de_salon)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;

-- Un ticket ocurre en un salón.
ALTER TABLE TICKETS
    ADD CONSTRAINT fk_tickets_salon
    FOREIGN KEY (numero_de_salon, tipo_de_salon)
    REFERENCES SALON (numero_de_salon, tipo_de_salon);

-- El aviso de estado pertenece a un ticket.
ALTER TABLE AVISO_DE_ESTADO
    ADD CONSTRAINT fk_aviso_de_estado_ticket
    FOREIGN KEY (id_ticket)
    REFERENCES TICKETS (id_ticket);

-- El registro diario pertenece a un ticket.
ALTER TABLE REGISTRO_DIARIO
    ADD CONSTRAINT fk_registro_diario_ticket
    FOREIGN KEY (id_ticket)
    REFERENCES TICKETS (id_ticket);

-- Una solicitud cumplida pertenece a una solicitud y a un técnico.
ALTER TABLE SOLICITUD_CUMPLIDA
    ADD CONSTRAINT fk_solicitud_cumplida_solicitud
    FOREIGN KEY (id_solicitud)
    REFERENCES SOLICITUD (id_solicitud);

ALTER TABLE SOLICITUD_CUMPLIDA
    ADD CONSTRAINT fk_solicitud_cumplida_tecnico
    FOREIGN KEY (documento_identidad_tecnico)
    REFERENCES TECNICO (documento_identidad);

-- El registro de clases es de un docente y de un salón.
ALTER TABLE REGISTRO_DIARIO_DOCENTE
    ADD CONSTRAINT fk_registro_docente
    FOREIGN KEY (documento_identidad)
    REFERENCES DOCENTE (documento_identidad);

ALTER TABLE REGISTRO_DIARIO_DOCENTE
    ADD CONSTRAINT fk_registro_salon
    FOREIGN KEY (numero_de_salon, tipo_de_salon)
    REFERENCES SALON (numero_de_salon, tipo_de_salon);

-- Un técnico resuelve tickets.
ALTER TABLE SOLUCIONA
    ADD CONSTRAINT fk_soluciona_ticket
    FOREIGN KEY (id_ticket)
    REFERENCES TICKETS (id_ticket);

ALTER TABLE SOLUCIONA
    ADD CONSTRAINT fk_soluciona_tecnico
    FOREIGN KEY (documento_identidad)
    REFERENCES TECNICO (documento_identidad);

-- Un docente hace solicitudes.
ALTER TABLE PIDE
    ADD CONSTRAINT fk_pide_solicitud
    FOREIGN KEY (id_solicitud)
    REFERENCES SOLICITUD (id_solicitud);

ALTER TABLE PIDE
    ADD CONSTRAINT fk_pide_docente
    FOREIGN KEY (documento_identidad)
    REFERENCES DOCENTE (documento_identidad);

-- Un docente reporta tickets.
ALTER TABLE HACE
    ADD CONSTRAINT fk_hace_docente
    FOREIGN KEY (documento_identidad)
    REFERENCES DOCENTE (documento_identidad);

ALTER TABLE HACE
    ADD CONSTRAINT fk_hace_ticket
    FOREIGN KEY (id_ticket)
    REFERENCES TICKETS (id_ticket);

-- Un administrador crea usuarios.
ALTER TABLE CREA
    ADD CONSTRAINT fk_crea_administrador
    FOREIGN KEY (administrador)
    REFERENCES ADMINISTRADOR (documento_identidad);

ALTER TABLE CREA
    ADD CONSTRAINT fk_crea_usuario
    FOREIGN KEY (usuario)
    REFERENCES USUARIO (documento_identidad);
