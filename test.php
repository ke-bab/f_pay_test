<?php

use FpDbTest\Database;
use FpDbTest\DatabaseTest;

spl_autoload_register(function ($class) {
    $a = array_slice(explode('\\', $class), 1);
    if (!$a) {
        throw new Exception();
    }
    $filename = implode('/', [__DIR__, ...$a]) . '.php';
    require_once $filename;
});

/**
 * соединение с бд я убрал т.к. тест работает и без него - мы же просто проверяем строки в финале.
 */

try {
    $db = new Database();
    $test = new DatabaseTest($db);
    $test->testBuildQuery();
    exit("OK\n");
} catch (\Exception $e) {
    echo "Not OK\n";
    echo "{$e->getMessage()}\n";
    exit();
}

