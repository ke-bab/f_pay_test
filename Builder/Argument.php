<?php

namespace FpDbTest\Builder;

class Argument
{
    private int $castBy;
    private int $pos;
    private mixed $data;

    public function __construct(string $query, int $pos, mixed $arg)
    {
//        echo $arg . "\n";
        $this->castBy = self::detectCastType($query, $pos);
        $this->pos = $pos;
        $this->data = $arg;
    }

    public function getPos(): int
    {
        return $this->pos;
    }

    public function getString(): string
    {
        if (is_string($this->data)) {
            return "'$this->data'";
        }

        return '';
    }

    public function getLen(): int
    {
        return $this->castBy === CastTypes::CAST_BY_VALUE ? 1 : 2;
    }

    private static function detectCastType(string $query, int $pos): int
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
}