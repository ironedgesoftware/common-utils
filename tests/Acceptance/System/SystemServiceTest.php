<?php
/*
 * This file is part of the common-utils package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\CommonUtils\Test\Acceptance\System;

use IronEdge\Component\CommonUtils\Exception\CommandException;
use IronEdge\Component\CommonUtils\System\SystemService;
use IronEdge\Component\CommonUtils\Test\Acceptance\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class SystemServiceTest extends AbstractTestCase
{
    public function test_executeCommand_shouldThrowExceptionIfCommandFails()
    {
        $cmd = 'echoasdasdasdas';
        $arguments = ['Hola', 'Mundo!'];
        $options = [
            'overrideExitCode'  => 111,
            'postCommand'       => ' 2> /dev/null'
        ];
        $expectedOptions = [
            'overrideExitCode'  => 111,
            'exceptionMessage'  => 'There was an error while executing the command.',
            'returnString'      => false,
            'implodeSeparator'  => PHP_EOL,
            'postCommand'       => ' 2> /dev/null'
        ];
        $systemService = $this->createInstance();

        try {
            $systemService->executeCommand($cmd, $arguments, $options);
        } catch (CommandException $e) {
            $expectedCommand = $cmd.' \'Hola\' \'Mundo!\' 2> /dev/null';

            $this->assertEquals($expectedOptions['exceptionMessage'], $e->getMessage());
            $this->assertEquals($expectedOptions['overrideExitCode'], $e->getCode());
            $this->assertEquals($expectedCommand, $e->getCmd());
            $this->assertEquals($arguments, $e->getArguments());
            $this->assertEquals([], $e->getOutput());
            $this->assertEquals([], $systemService->getLastExecutedCommandOutput());
            $this->assertEquals($expectedCommand, $systemService->getLastExecutedCommand());
            $this->assertEquals($arguments, $systemService->getLastExecutedCommandArguments());
            $this->assertEquals($expectedOptions, $systemService->getLastExecutedCommandOptions());
            $this->assertEquals($expectedOptions['overrideExitCode'], $systemService->getLastExecutedCommandExitCode());

            return;
        }

        $this->assertTrue(false, 'Test shouldn\'t reach here because command should throw an exception.');
    }

    public function test_executeCommand_shouldExecuteCommandSuccessfully()
    {
        $cmd = 'echo';
        $arguments = ['Hola', 'Mundo!'];
        $options = [];
        $expectedOptions = [
            'overrideExitCode'  => null,
            'exceptionMessage'  => 'There was an error while executing the command.',
            'returnString'      => false,
            'implodeSeparator'  => PHP_EOL,
            'postCommand'       => ''
        ];
        $expectedOutput = ['Hola Mundo!'];
        $systemService = $this->createInstance();

        $output = $systemService->executeCommand($cmd, $arguments, $options);

        $this->assertEquals($expectedOutput, $output);
        $this->assertEquals('echo \'Hola\' \'Mundo!\'', $systemService->getLastExecutedCommand());
        $this->assertEquals($arguments, $systemService->getLastExecutedCommandArguments());
        $this->assertEquals($expectedOptions, $systemService->getLastExecutedCommandOptions());
        $this->assertEquals(0, $systemService->getLastExecutedCommandExitCode());

        $expectedOutput = 'Hola Mundo!';

        $options['returnString'] = true;
        $expectedOptions['returnString'] = true;

        $output = $systemService->executeCommand($cmd, $arguments, $options);

        $this->assertEquals($expectedOutput, $output);
        $this->assertEquals('echo \'Hola\' \'Mundo!\'', $systemService->getLastExecutedCommand());
        $this->assertEquals($arguments, $systemService->getLastExecutedCommandArguments());
        $this->assertEquals($expectedOptions, $systemService->getLastExecutedCommandOptions());
        $this->assertEquals(0, $systemService->getLastExecutedCommandExitCode());
    }


    // Helper methods

    /**
     * @return SystemService
     */
    protected function createInstance()
    {
        return new SystemService();
    }
}