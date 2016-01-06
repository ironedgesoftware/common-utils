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

use IronEdge\Component\CommonUtils\Data\Data;

$data = new Data(
    [
        'user'          => [
            'username'      => 'my_user',
            'groups'        => [
                'primary'       => 'administrator',
                'secondary'     => [
                    'development'
                ]
            ]
        ]
    ]
);

echo PHP_EOL;

// It should return: 'administrator'.

print_r('Before changing "user.groups.primary": '.$data->get('user.groups.primary'));

echo PHP_EOL.PHP_EOL;

$data->set('user.groups.primary', 'new_group');

// It should return: 'new_group'.

print_r('After changing "user.groups.primary": '.$data->get('user.groups.primary'));

echo PHP_EOL.PHP_EOL;