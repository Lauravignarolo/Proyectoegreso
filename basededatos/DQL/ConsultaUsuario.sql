SELECT
                u.cedula,
                u.claveHash,
                u.activo,

                CASE
                    WHEN a.cedula IS NOT NULL THEN 1
                    ELSE 0
                END AS administrador,

                CASE
                    WHEN d.cedula IS NOT NULL THEN 1
                    ELSE 0
                END AS docente,

                CASE
                    WHEN di.cedula IS NOT NULL THEN 1
                    ELSE 0
                END AS direccion,

                CASE
                    WHEN t.cedula IS NOT NULL THEN 1
                    ELSE 0
                END AS tecnico,

            FROM USUARIO AS u

            LEFT JOIN ADMINISTRADOR AS a
                ON a.cedula = u.cedula

            LEFT JOIN DOCENTE AS d
                ON d.cedula = u.cedula

            LEFT JOIN DIRECCION AS di
                ON di.cedula = u.cedula

            LEFT JOIN TECNICO AS t
                ON t.cedula = u.cedula

            WHERE u.cedula = :cedula


SELECT
    u.cedula,
    u.nombre,
    u.apellido,

    CASE
        WHEN a.cedula IS NOT NULL THEN 1
        ELSE 0
    END AS administrador,

    CASE
        WHEN l.cedula IS NOT NULL THEN 1
        ELSE 0
    END AS logistica

FROM USUARIO AS u

LEFT JOIN ADMINISTRADOR AS a
    ON a.cedula = u.cedula

LEFT JOIN LOGISTICA AS l
    ON l.cedula = u.cedula
