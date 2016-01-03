# common-utils

Common utilities, simple to integrate in any project.


## DataTrait

This trait gives you a powerful API to access and manipulate data in an array. It has
simple methods to access / set / check of presence of elements at any depth in an array.

**NOTE**: This trait uses [OptionsTrait](#optionstrait).

Usage:

``` php
<?php

namespace Your\Namespace;

use IronEdge\Component\CommonUtils\Options\DataTrait;

class YourClass
{
    use DataTrait;


    // You need to implement this method to set the
    // default options of OptionsTrait

    public function getDefaultOptions()
    {
        return [
            'enabled'       => true
        ];
    }
}

$yourClass = new YourClass();

// Set the initial data

$originalData = [
    'user' => [
        'username'  => 'foo',
        'profile'   => 'custom_profile',
        'groups'    => [
            'primary'       => 'administrator',
            'secondary'     => ['development']
        ]
    ],
    'defaultUser'   => '%my_username%',
    'allowedIps'    => [
        '127.0.0.1'     => [
            'grants'        => 'all'
        ]
    ]
];

$yourClass->setData($originalData);

// Obtain user array. Result: ['username' => 'foo', 'profile' => ... ]

$yourClass->get('user');

// Obtain data at any depth. Result: 'administrator'

$yourClass->get('user.groups.primary');

// Use default value if an attribute is not set. Result: 'defaultValue'

$yourClass->get('iDontExist', 'defaultValue');

// Check if an attribute exist. Result: true

$yourClass->has('user.group.primary');

// This time, it returns: false

$yourClass->has('user.group.admin');

// You can use template variables that will be replaced
// when you set the data.

// First, it returns: '%my_username%'

$yourClass->get('defaultUser');

$yourClass->setOptions(
    [
        'templateVariables'         => [
            '%my_username%'             => 'admin'
        ]
    ]
);
$yourClass->setData($originalData);

// Now it returns: 'admin'

$yourClass->get('defaultUser');

// By default, element separator is '.'. You can change it
// overriding the default options, or on demand like in the following code.
// It should return: 'all'

$yourClass->get('allowedIps|127.0.0.1|grants', null, ['separator' => '|']);
```



## OptionsTrait

This trait allows you to add a simple Options API to your classes.

Usage:

``` php
<?php

namespace Your\Namespace;

use IronEdge\Component\CommonUtils\Options\OptionsTrait;

class YourClass
{
    use OptionsTrait;


    // You need to implement this method to set the
    // default options of OptionsTrait

    public function getDefaultOptions()
    {
        return [
            'enabled'       => true
        ];
    }
}

$yourClass = new YourClass();

// true

$yourClass->getOption('enabled');

// 'defaultValue'

$yourClass->getOption('iDontExist!', 'defaultValue');

// Set new options.

$yourClass->setOptions(['newOption' => 'newValue']);

// true - 'enabled' is still present as it's a default option.

$yourClass->hasOption('enabled');
$yourClass->getOption('enabled');

// 'newValue'

$yourClass->getOption('newOption');

// ['enabled' => true, 'newOption' => 'newValue']

$yourClas->getOptions();
```


