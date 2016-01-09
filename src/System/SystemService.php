<?php
declare(strict_types=1);

/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\CommonUtils\System;

use IronEdge\Component\CommonUtils\Exception\CommandException;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class SystemService
{
    /**
     * Last executed command.
     *
     * @var string|null
     */
    private $_lastExecutedCommand;

    /**
     * Last executed command arguments.
     *
     * @var array|null
     */
    private $_lastExecutedCommandArguments;

    /**
     * Last executed command options.
     *
     * @var array|null
     */
    private $_lastExecutedCommandOptions;

    /**
     * Last executed command output.
     *
     * @var array|null
     */
    private $_lastExecutedCommandOutput;

    /**
     * Field _lastExecutedCommandExitCode.
     *
     * @var int|null
     */
    private $_lastExecutedCommandExitCode;


    /**
     * Returns the value of field _lastExecutedCommand.
     *
     * @return string|null
     */
    public function getLastExecutedCommand()
    {
        return $this->_lastExecutedCommand;
    }

    /**
     * Returns the value of field _lastExecutedCommandArguments.
     *
     * @return array|null
     */
    public function getLastExecutedCommandArguments()
    {
        return $this->_lastExecutedCommandArguments;
    }

    /**
     * Returns the value of field _lastExecutedCommandOptions.
     *
     * @return array|null
     */
    public function getLastExecutedCommandOptions()
    {
        return $this->_lastExecutedCommandOptions;
    }

    /**
     * Returns the value of field _lastExecutedCommandOutput.
     *
     * @return array|null
     */
    public function getLastExecutedCommandOutput()
    {
        return $this->_lastExecutedCommandOutput;
    }

    /**
     * Returns the last command's exit code.
     *
     * @return int|null
     */
    public function getLastExecutedCommandExitCode()
    {
        return $this->_lastExecutedCommandExitCode;
    }

    /**
     * Executes a Command.
     *
     * @param string $cmd       - Command.
     * @param array  $arguments - Arguments.
     * @param array  $options   - Options.
     *
     * @throws CommandException
     *
     * @return array|string
     */
    public function executeCommand($cmd, array $arguments = [], array $options = [])
    {
        $options = array_replace(
            [
                'overrideExitCode'  => null,
                'exceptionMessage'  => 'There was an error while executing the command.',
                'returnString'      => false,
                'implodeSeparator'  => PHP_EOL,
                'postCommand'       => ''
            ],
            $options
        );

        if ($arguments) {
            foreach ($arguments as $arg) {
                $cmd .= ' '.escapeshellarg($arg);
            }
        }

        $cmd .= $options['postCommand'];

        exec($cmd, $output, $status);

        $status = $status && $options['overrideExitCode'] ?
            $options['overrideExitCode'] :
            $status;

        $this->_lastExecutedCommand = $cmd;
        $this->_lastExecutedCommandArguments = $arguments;
        $this->_lastExecutedCommandOptions = $options;
        $this->_lastExecutedCommandOutput = $output;
        $this->_lastExecutedCommandExitCode = $status;

        if ($status) {
            throw CommandException::create(
                $options['exceptionMessage'],
                $options['overrideExitCode'] ? $options['overrideExitCode'] : $status,
                $output,
                $cmd,
                $arguments
            );
        }

        return $options['returnString'] ?
            implode($options['implodeSeparator'], $output) :
            $output;
    }
}