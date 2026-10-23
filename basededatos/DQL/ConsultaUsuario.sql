-- ===========================================================================
-- Consulta de referencia: usuario con sus roles.
-- ===========================================================================
-- No la ejecuta la aplicación: UsuarioDAO::buscarPorCedula() arma esta misma
-- consulta. Sirve para probar el esquema a mano contra el DDL vigente.
--
-- Devuelve los mismos nombres de columna que espera la entidad Usuario,
-- para poder compararla con UsuarioDAO sin traducir campos.
-- ===========================================================================

SELECT
    u.documento_identidad,
    u.contrasena,
    u.estado,
    u.nombre,
    u.apellido,

    CASE WHEN a.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
        AS administrador,
    CASE WHEN d.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
        AS docente,
    CASE WHEN di.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
        AS direccion,
    CASE WHEN t.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
        AS tecnico

FROM USUARIO AS u

LEFT JOIN ADMINISTRADOR AS a
    ON a.documento_identidad = u.documento_identidad
LEFT JOIN DOCENTE AS d
    ON d.documento_identidad = u.documento_identidad
LEFT JOIN DIRECCION AS di
    ON di.documento_identidad = u.documento_identidad
LEFT JOIN TECNICO AS t
    ON t.documento_identidad = u.documento_identidad

WHERE u.documento_identidad = :cedula;
