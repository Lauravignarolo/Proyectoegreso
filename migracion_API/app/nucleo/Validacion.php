<?php

/**
 * Helpers de validación de la entrada.
 *
 * Todas las validaciones de formato viven acá para que los controladores
 * no repitan las mismas expresiones regulares. Cada método devuelve el
 * valor ya normalizado, o null cuando el dato no es válido.
 */
class Validacion
{
    /**
     * CédulaBQ de 8 dígitos, sin cero inicial (char(8) en el DDL).
     */
    public static function cedula(mixed $valor): ?string
    {
        if (!is_string($valor) && !is_int($valor)) {
            return null;
        }

        $cedula = trim((string) $valor);

        return preg_match("/^[1-9][0-9]{7}$/", $cedula) === 1 ? $cedula : null;
    }

    /**
     * Texto obligatorio (o opcional) con control de largo.
     *
     * @param mixed  $valor      Valor a validar.
     * @param int    $maximo     Largo máximo permitido.
     * @param bool   $obligatorio Si es false, un texto vacío devuelve "".
     */
    public static function texto(
        mixed $valor,
        int $maximo = 255,
        bool $obligatorio = true
    ): ?string {
        if (!is_scalar($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        if ($texto === "") {
            return $obligatorio ? null : "";
        }

        return mb_strlen($texto) <= $maximo ? $texto : null;
    }

    /**
     * Entero dentro del rango pedido.
     */
    public static function entero(mixed $valor, int $minimo = 1): ?int
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $entero = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => $minimo]]
        );

        return $entero === false ? null : (int) $entero;
    }

    /**
     * Hora en formato HH:MM (columnas TIME del DDL).
     */
    public static function hora(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $hora = trim($valor);

        return preg_match("/^(?:[01]\d|2[0-3]):[0-5]\d$/", $hora) === 1
            ? $hora
            : null;
    }

    /**
     * Fecha en formato YYYY-MM-DD (columna DATE del DDL).
     */
    public static function fecha(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $fecha = trim($valor);
        $objeto = DateTimeImmutable::createFromFormat("!Y-m-d", $fecha);

        if (!$objeto || $objeto->format("Y-m-d") !== $fecha) {
            return null;
        }

        return $fecha;
    }

    /**
     * Verifica que el valor esté dentro de una lista de valores permitidos.
     */
    public static function enLista(mixed $valor, array $permitidos): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $valor = trim($valor);

        return in_array($valor, $permitidos, true) ? $valor : null;
    }
}
