<?php
/*
 * This file is part of the common-utils package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\CommonUtils\Test\Unit\Options;

use IronEdge\Component\CommonUtils\Options\OptionsInterface;
use IronEdge\Component\CommonUtils\Options\OptionsTrait;
use IronEdge\Component\CommonUtils\Test\Unit\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class OptionsTraitTest extends AbstractTestCase
{
    public function test_setOptions_setsOptions()
    {
        $options = $this->createInstance();
        $optionsData = ['option1' => 'value1'];

        $this->assertEmpty($options->getOptions());

        $options->setOptions($optionsData);

        $this->assertEquals($optionsData, $options->getOptions());
    }

    public function test_setOption_setsASpecificOption()
    {
        $options = $this->createInstance();
        $optionsData = ['option1' => 'option1'];

        $this->assertEmpty($options->getOptions());

        $options->setOptions($optionsData);
        $options->setOption('newOption', 'newValue');

        $this->assertEquals('newValue', $options->getOption('newOption'));
        $this->assertEquals('option1', $options->getOption('option1'));
    }

    public function test_getOption_returnsDefaultIfOptionDoesNotExist()
    {
        $options = $this->createInstance();

        $this->assertEquals('12345343', $options->getOption('iDontExist!', '12345343'));
    }


    // Helper methods

    /**
     * @return OptionsInterface
     */
    protected function createInstance()
    {
        return new class implements OptionsInterface {
            use OptionsTrait;
        };
    }
}