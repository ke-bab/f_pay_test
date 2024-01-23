<?php

namespace FpDbTest\Builder;

class Argument
{
    private int $castBy;
    private ?string $annotation;
    private int $pos;
    private mixed $data;

    public function __construct(string $query, int $pos, mixed $arg)
    {
        $this->castBy = CastTypes::detectCastType($query, $pos);
        $this->pos = $pos;
        $this->data = $arg;
        $this->annotation = $this->castBy === CastTypes::CAST_BY_VALUE ? null : $query[$pos + 1];
    }

    public function getPos(): int
    {
        return $this->pos;
    }

    /**
     * @throws \Exception
     */
    public function getString(): string
    {
        return (string) CastTypes::getValue($this->castBy, $this->annotation, $this->data);
    }

    /**
     * Сколько символов заменить в исходной строке, 1 или 2 (? или ?d к примеру)
     */
    public function getLen(): int
    {
        return $this->castBy === CastTypes::CAST_BY_VALUE ? 1 : 2;
    }

    public function getAnnotation(): string
    {
        return $this->annotation ?? '';
    }
}