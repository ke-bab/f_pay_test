<?php

namespace FpDbTest;

use Exception;
use FpDbTest\Builder\Argument;
use FpDbTest\Builder\Casting\CastTypes;
use mysqli;

class Database implements DatabaseInterface
{
    private bool $skipOptional;

    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function buildQuery(string $query, array $args = []): string
    {
        $questionMarksCount = 0;
        // поискать все вхождения паттернов с вопросиком
        $argsCount = count($args);
        /** @var Argument[] $foundReplacements */
        $foundReplacements = [];
        $newQuery = $query;
        $offset = 0;
        while (true) {
            $pos = strpos($query, "?", $offset);
            if ($pos === false) {
                break;
            }
            $questionMarksCount += 1;
            $offset = $pos + 1;
            // если аргументов передано меньше чем мест замены для них в строке
            if ($questionMarksCount > $argsCount) {
                throw new Exception("argument count and question mark count don't match");
            }

            $foundReplacements[] = new Argument($query, $pos, $args[$questionMarksCount - 1]);
        }
        // если аргументов передано больше чем мест замены для них в строке
        if ($questionMarksCount < $argsCount) {
            throw new Exception("argument count and question mark count don't match");
        }
        // заменить каждый паттерн на данные нужного типа
        foreach ($foundReplacements as $replacement) {
            $newQuery = substr_replace(
                $newQuery,
                $replacement->getString(),
                $replacement->getPos(),
                $replacement->getLen()
            );
        }

        return $newQuery;
    }

    public function skip()
    {
        $this->skipOptional = true;
    }
}
