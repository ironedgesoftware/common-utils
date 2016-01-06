# common-utils

[![Build Status](https://travis-ci.org/ironedgesoftware/common-utils.svg?branch=master)](https://travis-ci.org/ironedgesoftware/common-utils)

Common utilities, simple to integrate in any project.

## Index

* [DataTrait](#datatrait): A Trait to create your own configuration classes.
* [Data](#data): A class using [DataTrait](#datatrait) so you can start using it right from scratch.
* [OptionsTrait](#optionstrait): A Trait to add a simple options API to your classes.
* [Example Files](#example-files): A list of example PHP files that we provide so you can see how to use the features of this component.


## DataTrait

This trait gives you a powerful API to access and manipulate data in an array. It has
simple methods to access / set / check of presence of elements at any depth in an array.

**NOTE**: This trait uses [OptionsTrait](#optionstrait).

Usage:

``` php
<?php

require_once('/path/to/autoload.php');

use IronEdge\Component\CommonUtils\Data\DataTrait;

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

$yourClass->has('user.groups.primary');

// This time, it returns: false

$yourClass->has('user.groups.admin');

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

// We need to set the data again so the template variables get replaced.

$yourClass->setData($originalData);

// Now it returns: 'admin'

$yourClass->get('defaultUser');

// By default, element separator is '.'. You can change it
// overriding the default options, or on demand like in the following code.
// It should return: 'all'

$yourClass->get('allowedIps|127.0.0.1|grants', null, ['separator' => '|']);
```

## Data

We also include a class that already uses the [DataTrait](#datatrait).

``` php
<?php

require_once('/path/to/autoload.php');

use IronEdge\Component\CommonUtils\Data\Data;

$myData = new Data(
    [
        'myParameter'   => 'myValue'
    ]
);

// Should return 'myValue'.

$myData->get('myParameter');
```

## OptionsTrait

This trait allows you to add a simple Options API to your classes.

Usage:

``` php
<?php

require_once('/path/to/autoload.php');

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

## Example Files

With this component we provide a set of simple example PHP files implementing the
features of this component. You can just go to the examples directories and execute them:

``` bash
cd resources/examples;

# Replace here "example-file.php" with the example file name you want to execute.

php example-file.php;
```

* **resources/examples/data.php**: Shows the usage of the [Data class](#data).