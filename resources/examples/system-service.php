<?php

/*
 * This file is part of the common-utils package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../autoload.php';

$systemService = new \IronEdge\Component\CommonUtils\System\SystemService();

echo PHP_EOL.'Example: "$systemService->executeCommand(\'ls\', [\'-la\']);"';

echo PHP_EOL.PHP_EOL;

print_r($systemService->executeCommand('ls', ['-la']));

echo PHP_EOL;

echo '----------------------------------------------------'.PHP_EOL;

echo PHP_EOL.'Example: "$systemService->executeCommand(\'ls -la\']);"';

echo PHP_EOL.PHP_EOL;

print_r($systemService->executeCommand('ls -la'));

echo PHP_EOL;

echo '----------------------------------------------------'.PHP_EOL;

echo PHP_EOL.'Example: "$systemService->executeCommand(\'ls -la\'], [], [\'returnString\' => true]);"';

echo PHP_EOL.PHP_EOL;

print_r($systemService->executeCommand('ls -la', [], ['returnString' => true]).PHP_EOL.PHP_EOL);