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

use IronEdge\Component\CommonUtils\Exception\CantRemoveDirectoryException;
use IronEdge\Component\CommonUtils\Exception\CommandException;
use IronEdge\Component\CommonUtils\Exception\IOException;
use IronEdge\Component\CommonUtils\Exception\NotADirectoryException;


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
    private $_lastExecutedCommand = [
        'cmd'               => null,
        'arguments'         => null,
        'options'           => null,
        'output'            => null,
        'exitCode'          => null
    ];


    /**
     * Returns the value of field _lastExecutedCommand.
     *
     * @return string|null
     */
    public function getLastExecutedCommand()
    {
        return $this->_lastExecutedCommand['cmd'];
    }

    /**
     * Returns the value of field _lastExecutedCommandArguments.
     *
     * @return array|null
     */
    public function getLastExecutedCommandArguments()
    {
        return $this->_lastExecutedCommand['arguments'];
    }

    /**
     * Returns the value of field _lastExecutedCommandOptions.
     *
     * @return array|null
     */
    public function getLastExecutedCommandOptions()
    {
        return $this->_lastExecutedCommand['options'];
    }

    /**
     * Returns the value of field _lastExecutedCommandOutput.
     *
     * @return array|null
     */
    public function getLastExecutedCommandOutput()
    {
        return $this->_lastExecutedCommand['output'];
    }

    /**
     * Returns the last command's exit code.
     *
     * @return int|null
     */
    public function getLastExecutedCommandExitCode()
    {
        return $this->_lastExecutedCommand['exitCode'];
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
    public function executeCommand(string $cmd, array $arguments = [], array $options = [])
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

        foreach ($arguments as $arg) {
            $cmd .= ' '.escapeshellarg($arg);
        }

        $cmd .= $options['postCommand'];

        exec($cmd, $output, $status);

        $status = $status && $options['overrideExitCode'] ?
            $options['overrideExitCode'] :
            $status;

        $this->_lastExecutedCommand['cmd'] = $cmd;
        $this->_lastExecutedCommand['arguments'] = $arguments;
        $this->_lastExecutedCommand['options'] = $options;
        $this->_lastExecutedCommand['output'] = $output;
        $this->_lastExecutedCommand['exitCode'] = $status;

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

    /**
     * Creates a directory. If it already exists, it doesn't throw an exception.
     *
     * Options:
     *
     * - mode:      Default 0777
     * - recursive: Create missing directories recursively.
     * - context:   Stream context.
     *
     * @param string $dir     - Directory.
     * @param array  $options - Options.
     *
     * @throws IOException
     *
     * @return SystemService
     */
    public function mkdir(string $dir, array $options = []): SystemService
    {
        $options = array_replace(
            [
                'mode'          => 0777,
                'recursive'     => true,
                'context'       => null
            ],
            $options
        );

        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw NotADirectoryException::create(
                    'Can\'t create directory "'.$dir.'" because it exists and it\'s not a directory.'
                );
            }

            return $this;
        }

        $args = [
            $dir,
            $options['mode'],
            $options['recursive']
        ];

        if ($options['context'] !== null) {
            $args[] = $options['context'];
        }

        if (!@mkdir(...$args)) {
            $lastError = $this->getLastPhpError();

            throw IOException::create(
                'Couldn\'t create directory "'.$dir.'". PHP Error: '.print_r($lastError, true)
            );
        }

        return $this;
    }

    /**
     * Removes a file, symlink or directory. It removes directories, if option "force" is true.
     * If it's a directory and option "force" is false, it throws an exception.
     * Also, it removes directories recursively. If file, symlink or directory already does not
     * exist, it does NOT throw an exception.
     *
     * Options:
     *
     * - force:         If $fileOrDirectory is a directory, you need to set this option to "true"
     *                  to be able to remove it. By default, it's set to "false".
     * - recursive:     If this option is "true" and $fileOrDirectory is a directory, then we'll
     *                  traverse it and remove every file and directory found on it as well. By
     *                  default, it's set to "false".
     *
     * @param string $fileOrDirectory - File, symlink or directory.
     * @param array  $options         - Options.
     *
     * @throws IOException
     *
     * @return SystemService
     */
    public function rm(string $fileOrDirectory, array $options = []): SystemService
    {
        $options = array_replace(
            [
                'force'                 => false,
                'recursive'             => false,
                'context'               => null,
                'skipIfAlreadyRemoved'  => true
            ],
            $options
        );

        if (!file_exists($fileOrDirectory) && $options['skipIfAlreadyRemoved']) {
            return $this;
        }

        $args = [
            $fileOrDirectory
        ];

        if ($options['context'] !== null) {
            $args[] = $options['context'];
        }

        if (is_dir($fileOrDirectory) && !is_link($fileOrDirectory)) {
            if (!$options['force']) {
                throw CantRemoveDirectoryException::create(
                    '"' . $fileOrDirectory . '" is a directory. If you really want to remove it, ' .
                    'set the "force" option to "true".'
                );
            }

            if ($options['recursive']) {
                $elements = $this->scandir(
                    $fileOrDirectory,
                    [
                        'recursive'         => true,
                        'context'           => $options['context'],
                        'skipDots'          => true,
                        'skipSymlinks'      => true
                    ]
                );

                foreach ($elements as $e) {
                    $this->rm($e, $options);
                }
            }

            if (!@rmdir(...$args)) {
                throw IOException::create(
                    'Couldn\'t remove directory "'.$fileOrDirectory.'". Last PHP Error: '.
                    print_r($this->getLastPhpError(), true)
                );
            }
        } else if (!@unlink(...$args)) {
            throw IOException::create(
                'Couldn\'t remove file or symlink "'.$fileOrDirectory.'". Last PHP Error: '.
                print_r($this->getLastPhpError(), true)
            );
        }

        return $this;
    }

    /**
     * Scans a directory and returns an array of files, symlinks and directories.
     *
     * Options:
     *
     * - sort:              One of the SCANDIR_SORT_* constants. Defaults to SCANDIR_SORT_NONE.
     * - context:           Context to use for the scandir function. Defaults to "null".
     * - recursive:         If this option is "true", we'll call this method for every directory
     *                      found. Defaults to "false".
     * - skipDots:          If "true", then "." and ".." won't be returned. Defaults to "true".
     * - skipSymlinks:      If "true", then symlinks will be skipped. Defaults to "true". Please note
     *                      that this option also applies if the $dir argument is a symlink. If this
     *                      option is false then we will call "readlink" to determine where the symlink
     *                      points to.
     *
     * @param string $dir     - Directory to scan.
     * @param array  $options - Options.
     *
     * @throws IOException
     *
     * @return array
     */
    public function scandir(string $dir, array $options = [])
    {
        $options = array_replace(
            [
                'sort'          => SCANDIR_SORT_NONE,
                'context'       => null,
                'recursive'     => false,
                'skipDots'      => true,
                'skipSymlinks'  => false
            ],
            $options
        );

        if (!$options['skipSymlinks'] && is_link($dir)) {
            $dir = @readlink($dir);
        }

        $args = [
            $dir,
            $options['sort']
        ];

        if ($options['context']) {
            $args[] = $options['context'];
        }

        if (($tmp = @scandir(...$args)) === false) {
            throw IOException::create(
                'Couldn\'t scan directory "'.$dir.'". Last PHP Error: '.
                print_r($this->getLastPhpError(), true)
            );
        }

        if ($options['skipDots']) {
            $tmp = array_diff($tmp, array('.', '..'));
        }

        $result = [];

        foreach ($tmp as $f) {
            $f = $dir.'/'.$f;

            if ($options['skipSymlinks'] && is_link($f)) {
                continue;
            }

            $result[] = $f;

            if ($options['recursive'] && is_dir($f)) {
                $result = array_merge(
                    $result,
                    $this->scandir($f, $options)
                );
            }
        }

        $tmp = null;

        return array_unique($result);
    }

    /**
     * Returns the last PHP error.
     *
     * @return array|null
     */
    public function getLastPhpError()
    {
        return error_get_last();
    }
}