<?php
/*
 * This file is part of the common-utils package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\CommonUtils\Test\Unit\Data;

use IronEdge\Component\CommonUtils\Data\Data;
use IronEdge\Component\CommonUtils\Data\DataInterface;
use IronEdge\Component\CommonUtils\Data\DataTrait;
use IronEdge\Component\CommonUtils\Exception\DataIsReadOnlyException;
use IronEdge\Component\CommonUtils\Exception\InvalidCastException;
use IronEdge\Component\CommonUtils\Test\Unit\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class DataTraitTest extends AbstractTestCase
{
    /**
     * @dataProvider invalidCastDataProvider
     */
    public function test_getCastedValue_throwsInvalidCast(array $data, string $index, string $type) {
        $config = $this->createInstance($data);
        
        $this->expectException(get_class(new InvalidCastException()));
        
        $config->getCastedValue($type, $index);
    }
    
    /**
     * @dataProvider getBooleanDataProvider
     */
    public function test_getBoolean_returnsCastedValue(
        array $data,
        string $index,
        $expectedValue,
        $default = null,
        array $options = []
    ) {
        $config = $this->createInstance($data);
        
        $this->assertEquals($expectedValue, $config->getBoolean($index, $default, $options));
    }
    
    /**
     * @dataProvider getFloatDataProvider
     */
    public function test_getFloat_returnsCastedValue(
        array $data,
        string $index,
        $expectedValue,
        $default = null,
        array $options = []
    ) {
        $config = $this->createInstance($data);
        
        $this->assertEquals($expectedValue, $config->getFloat($index, $default, $options));
    }
    
    /**
     * @dataProvider getIntDataProvider
     */
    public function test_getInt_returnsCastedValue(
        array $data,
        string $index,
        $expectedValue,
        $default = null,
        array $options = []
    ) {
        $config = $this->createInstance($data);
        
        $this->assertEquals($expectedValue, $config->getInt($index, $default, $options));
    }
    
    /**
     * @dataProvider getCastedValueDataProvider
     */
    public function test_getCastedValue_returnsCastedValue(
        array $data,
        string $index,
        string $expectedType,
        $expectedValue,
        $default = null,
        array $options = []
    ) {
        $config = $this->createInstance($data);
        
        $this->assertEquals($expectedValue, $config->getCastedValue($expectedType, $index, $default, $options));
    }
    
    /**
     * @dataProvider removeDataProvider
     */
    public function test_remove_shouldRemoveElementFromDataArray(array $data, string $removeIndex)
    {
        $config = $this->createInstance($data);

        $config->remove($removeIndex);

        $this->assertFalse($config->has($removeIndex));
    }

    /**
     * @dataProvider readOnlyDataProvider
     */
    public function test_readOnly_ifInstanceIsReadOnlyThenThrowException(\Closure $setDataClosure)
    {
        $this->expectException(get_class(new DataIsReadOnlyException()));

        $config = $this->createInstance([]);

        $config->setReadOnly(true);

        $setDataClosure($config);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_callFunction_ifFunctionExistsThrowsException()
    {
        $config = $this->createInstance([]);
        $config->callFunction('function_does_not_exist!', 'a', ['b']);
    }

    /**
     * @dataProvider callFunctionDataProvider
     */
    public function test_callFunction($function, $objectFunction)
    {
        $originalData = ['username' => 'admin', 'groups' => ['admin' => 'enabled', 'guest' => 'disabled']];
        $config = $this->createInstance(['user' => $originalData]);
        $expectedData = ['email' => 'a@a.com', 'groups' => ['admin' => 'disabled', 'guest' => 'enabled', 'other' => 'disabled']];
        $config->$objectFunction('user', $expectedData);

        $this->assertEquals($function($originalData, $expectedData), $config->get('user'));
    }

    public function test_replaceTemplateVariables_replacesAStringAndAnArrayAsWell()
    {
        $templateVariables = [
            '%my_email%'            => 'a@a.com',
            '%my_age%'              => 21,
            '%my_group%'            => 'admin_group',
            '%isAdmin%'             => 'YES',
            '%car%'                 => 'Porsche 911'
        ];
        $config = $this->createInstance([], ['templateVariables' => $templateVariables]);

        $this->assertEquals(['email' => $templateVariables['%my_email%']], $config->replaceTemplateVariables(['email' => '%my_email%']));
        $this->assertEquals($templateVariables['%my_email%'].' is my email', $config->replaceTemplateVariables('%my_email% is my email'));
    }

    public function test_templateVariables_shouldBeReplacedAtAnyLevel()
    {
        $data = ['user' => array('email' => '%my_email%', 'profile' => array('age' => '%my_age%')), 'group' => '%my_group%'];
        $templateVariables = [
            '%my_email%'            => 'a@a.com',
            '%my_age%'              => 21,
            '%my_group%'            => 'admin_group',
            '%isAdmin%'             => 'YES',
            '%car%'                 => 'Porsche 911'
        ];
        $config = $this->createInstance($data, ['templateVariables' => $templateVariables]);

        $this->assertEquals($templateVariables['%my_email%'], $config->get('user.email'));
        $this->assertEquals($templateVariables['%my_age%'], $config->get('user.profile.age'));
        $this->assertEquals($templateVariables['%my_group%'], $config->get('group'));

        $data['isAdmin'] = '%isAdmin%';

        $config->setData($data);

        $this->assertEquals($templateVariables['%isAdmin%'], $config->get('isAdmin'));

        $config->setData($data, false);

        $this->assertEquals('%my_email%', $config->get('user.email'));
        $this->assertEquals('%my_age%', $config->get('user.profile.age'));
        $this->assertEquals('%my_group%', $config->get('group'));
        $this->assertEquals('%isAdmin%', $config->get('isAdmin'));

        $config->setData(['car' => '%car%']);

        $this->assertEquals($templateVariables['%car%'], $config->get('car'));
    }

    public function test_has_shouldReturnCorrectElement()
    {
        $data = ['user' => array('email' => 'test@test.com', 'profile' => array('age' => 15)), 'group' => 'internal'];
        $config = $this->createInstance($data);

        $this->assertTrue($config->has('user.email'));
        $this->assertFalse($config->has('user.password'));
    }

    public function test_set_get_has_ifOtherSeparatorIsSpecifiedThenUseIt()
    {
        $config = $this->createInstance();

        $config->set('testComponent|user|username', 'test', ['separator' => '|']);

        $this->assertTrue($config->has('testComponent|user|username', ['separator' => '|']));
        $this->assertEquals('test', $config->get('testComponent|user|username', null, ['separator' => '|']));
    }

    public function test_get_shouldReturnCorrectElement()
    {
        $data = ['user' => array('email' => 'test@test.com', 'profile' => array('age' => 15)), 'group' => 'internal'];
        $config = $this->createInstance($data);

        $this->assertEquals($data['group'], $config->get('group'));
        $this->assertEquals($data['user']['email'], $config->get('user.email'));
        $this->assertEquals($data['user']['profile']['age'], $config->get('user.profile.age'));
        $this->assertEquals('notFound!', $config->get('user.username', 'notFound!'));
    }

    public function test_set_setsValuesCorrectly()
    {
        $data = ['user' => array('email' => 'test@test.com', 'profile' => array('age' => 15)), 'group' => 'internal'];
        $config = $this->createInstance($data);

        $config->set('test', 'myValue');
        $config->set('test2.test3.test4', 'myOtherValue');

        $this->assertEquals('myValue', $config->get('test'));
        $this->assertEquals('myOtherValue', $config->get('test2.test3.test4'));
    }



    // Data Providers

    public function callFunctionDataProvider()
    {
        return [
            [
                'array_merge', 'merge'
            ],
            [
                'array_replace', 'replace'
            ],
            [
                'array_replace_recursive', 'replaceRecursive'
            ],
            [
                'array_merge_recursive', 'mergeRecursive'
            ]
        ];
    }

    public function removeDataProvider()
    {
        return [
            [
                [
                    'user'          => [
                        'profiles'      => [
                            'testProfile'   => [
                                'admin'         => 1
                            ]
                        ]
                    ]
                ],
                'user.profiles.testProfile.admin'
            ],
            [
                [
                    'user'          => [
                        'profiles'      => [
                            'testProfile'   => [
                                'admin'         => 1
                            ]
                        ]
                    ]
                ],
                'user.nonExistentKey'
            ],
            [
                [
                    'user'          => [
                        'profiles'      => [
                            'testProfile'   => [
                                'admin'         => 1
                            ]
                        ]
                    ]
                ],
                'user.profiles'
            ]
        ];
    }

    public function readOnlyDataProvider()
    {
        return [
            [
                function(Data $config) {
                    $config->set('a', 'b');
                }
            ],
            [
                function(Data $config) {
                    $config->merge('test', ['a' => 'b']);
                }
            ],
            [
                function(Data $config) {
                    $config->replace('test', ['a' => 'b']);
                }
            ],
            [
                function(Data $config) {
                    $config->mergeRecursive('test', ['a' => 'b']);
                }
            ],
            [
                function(Data $config) {
                    $config->replaceRecursive('test', ['a' => 'b']);
                }
            ],
            [
                function(Data $config) {
                    $config->setData(['b' => 'c']);
                }
            ],
            [
                function(Data $config) {
                    $config->set('test', []);
                    $config->callFunction('array_merge', 'test', ['b' => 'c']);
                }
            ]
        ];
    }
    
    public function getCastedValueDataProvider()
    {
        return [
            [
                ['myStringValue' => '11112222'],
                'myStringValue',
                'int',
                11112222
            ],
            [
                ['myStringValue' => 'asdasasd'],
                'myStringValue',
                'int',
                '0'
            ],
            [
                ['myIntValue' => 11233455],
                'myIntValue',
                'string',
                '11233455'
            ],
            [
                ['myIntValue' => 11233455],
                'myIntValue',
                'boolean',
                true
            ],
            [
                ['myIntValue' => 0],
                'myIntValue',
                'boolean',
                false
            ],
            [
                ['myStringValue' => '11233455123123122133131'],
                'myStringValue',
                'float',
                11233455123123122133131
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                'float',
                null
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                'float',
                '1234',
                '1234'
            ],
            [
                ['otherKey' => ['anotherKey' => '1234']],
                'otherKey|anotherKey',
                'float',
                1234,
                null,
                ['separator' => '|']
            ]
        ];
    }
    
    public function getIntDataProvider()
    {
        return [
            [
                ['myStringValue' => '11112222'],
                'myStringValue',
                11112222
            ],
            [
                ['myStringValue' => 'asdasasd'],
                'myStringValue',
                '0'
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                null
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                '1234',
                '1234'
            ],
            [
                ['otherKey' => ['anotherKey' => '1234']],
                'otherKey|anotherKey',
                1234,
                null,
                ['separator' => '|']
            ]
        ];
    }
    
    public function getFloatDataProvider()
    {
        return [
            [
                ['myStringValue' => '11233455123123122133131'],
                'myStringValue',
                11233455123123122133131
            ],
            [
                ['myStringValue' => 'asdasasd'],
                'myStringValue',
                0
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                null
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                '1234',
                '1234'
            ],
            [
                ['otherKey' => ['anotherKey' => '1234']],
                'otherKey|anotherKey',
                1234,
                null,
                ['separator' => '|']
            ]
        ];
    }
    
    public function getBooleanDataProvider()
    {
        return [
            [
                ['myStringValue' => '11233455123123122133131'],
                'myStringValue',
                true
            ],
            [
                ['myStringValue' => '0'],
                'myStringValue',
                false
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                null
            ],
            [
                ['otherKey' => '11233455123123122133131'],
                'myStringValue',
                true,
                true
            ],
            [
                ['otherKey' => ['anotherKey' => '1234']],
                'otherKey|anotherKey',
                true,
                null,
                ['separator' => '|']
            ]
        ];
    }
    
    public function invalidCastDataProvider()
    {
        return [
            [
                ['myValue' => []],
                'myValue',
                'int'
            ],
            [
                ['myValue' => new \DateTime()],
                'myValue',
                'int'
            ],
            [
                ['myValue' => '123'],
                'myValue',
                'inasdadt'
            ],
        ];
    }
    
    // Helper methods

    /**
     * @return DataInterface
     */
    protected function createInstance(array $data = [], array $options = [])
    {
        $instance = new Data($data, $options);

        return $instance;
    }
}