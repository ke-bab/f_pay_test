<?php

namespace FpDbTest\Builder;

/**
 * CastTypes хранит инфу о типах.
 * Какие типы можно приводить если нет аннотации - string, int, float, bool.
 * Какие типы бывают в аннотациях -
 * ?d - число
 * ?f - число с плав. точкой
 * ?a - массив либо мапа
 * ?# - одно значение либо массив значений (не мапа)
 */
class CastTypes
{
    const CAST_BY_VALUE = 0; // когда аннотации нет и мы проверяем тип данных аргумента, и приводим его к стандартному типу.
    const CAST_BY_ANNOTATION = 1; // когда есть аннотация типа ?a

    const TYPE_INT = 'd';
    const TYPE_FLOAT = 'f';
    const TYPE_ARRAY_OR_MAP = 'a';
    const TYPE_ARRAY_OR_SINGLE = '#';

    const TYPES = [
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_ARRAY_OR_MAP,
        self::TYPE_ARRAY_OR_SINGLE,
    ];


    public static function detectCastType(string $query, int $pos): int
    {
        if (!isset($query[$pos + 1])) {
            // если за ? конец строки - это дефолтный каст
            return CastTypes::CAST_BY_VALUE;
        }
        if (in_array($query[$pos + 1], CastTypes::TYPES)) {
            // если есть корректный тип - это каст по типу
            return CastTypes::CAST_BY_ANNOTATION;
        }

        // все остальные варианты - дефолтный каст (по значению)
        return CastTypes::CAST_BY_VALUE;
    }

    /**
     * @throws \Exception
     * @return string|int|float|bool
     */
    public static function getValue(int $castBy, ?string $annotation, mixed $data): mixed
    {
        if ($castBy === CastTypes::CAST_BY_VALUE) {
            if (!is_string($data) && !is_bool($data) && !is_int($data) && !is_float($data)) {
                $type = gettype($data);
                throw new \Exception("argument has wrong type ($type). allowed types: string, int, float, bool.");
            }
            if (is_string($data)) {
                $data = "'$data'";
            }

            return $data;
        } else {
            if ($annotation === self::TYPE_INT) {
                return (int)$data;
            }
            if ($annotation === self::TYPE_FLOAT) {
                return (float)$data;
            }
            if ($annotation === self::TYPE_ARRAY_OR_MAP) {
                return self::formatArray($data, "'");
            }
            if ($annotation === self::TYPE_ARRAY_OR_SINGLE) {
                if (is_array($data)) {
                    return self::formatArray($data, '`');
                } else {
                    return $data;
                }
            }
            throw new \Exception("annotation argument has wrong type");
        }
    }

    private static function cutTwoChars(string $str): string
    {
        return substr_replace($str, '', strlen($str) - 2, 2);
    }

    /**
     * @param array $a
     * @param string $ticks - нужен что бы задать тип кавычек для значений, т.к. они отличаются в некоторых случаях.
     * @return string
     */
    private static function formatArray(array $a, string $ticks): string
    {
        $str = '';
        $isMap = !array_is_list($a);
        foreach ($a as $key => $item) {
            if (is_string($item)) {
                $item = "$ticks$item$ticks";
            }
            if (is_null($item)) {
                $item = "NULL";

            }
            if ($isMap) {
                $str .= "`$key` = $item, ";
            } else {
                $str .= "$item, ";
            }
        }
        // отрежем запятую и пробел в конце
        return self::cutTwoChars($str);
    }
}