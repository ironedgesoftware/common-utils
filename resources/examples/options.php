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

use IronEdge\Component\CommonUtils\Options\OptionsTrait;

class MyClassWithOptions
{
    use OptionsTrait;

    public function getDefaultOptions(): array
    {
        return [
            'myTestOption'          => 'myTestOptionValue'
        ];
    }
}

$options = new MyClassWithOptions();

echo PHP_EOL;

// Should return 'myTestOptionValue'.

print_r('Option "myTestOption": '.$options->getOption('myTestOption'));

echo PHP_EOL.PHP_EOL;

// Should return 'myDefaultValue!'.

print_r('Option "iDontExist" - Should return a default value: '.$options->getOption('iDontExist', 'myDefaultValue!'));

echo PHP_EOL.PHP_EOL;