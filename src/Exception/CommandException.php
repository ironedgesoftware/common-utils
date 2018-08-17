<?php
declare(strict_types=1);

/*
 * This file is part of the common-utils package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\CommonUtils\Exception;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class CommandException extends BaseException
{
    /**
     * Command executed.
     *
     * @var string
     */
    private $_cmd;

    /**
     * Command arguments.
     *
     * @var array
     */
    private $_arguments = [];

    /**
     * Command Output.
     *
     * @var array
     */
    private $_output;

    /**
     * Returns the value of field _output.
     *
     * @return array
     */
    public function getOutput(): array
    {
        return $this->_output;
    }

    /**
     * Sets the value of field output.
     *
     * @param array $output - output.
     *
     * @return self
     */
    public function setOutput($output): self
    {
        $this->_output = $output;

        return $this;
    }

    /**
     * Returns the value of field _cmd.
     *
     * @return string
     */
    public function getCmd(): string
    {
        return $this->_cmd;
    }

    /**
     * Sets the value of field cmd.
     *
     * @param string $cmd - cmd.
     *
     * @return self
     */
    public function setCmd(string $cmd) : self
    {
        $this->_cmd = $cmd;

        return $this;
    }

    /**
     * Returns the value of field _arguments.
     *
     * @return array
     */
    public function getArguments() : array
    {
        return $this->_arguments;
    }

    /**
     * Sets the value of field arguments.
     *
     * @param array $arguments - arguments.
     *
     * @return self
     */
    public function setArguments($arguments) : self
    {
        $this->_arguments = $arguments;

        return $this;
    }

    /**
     * Creates an instance of this exception.
     *
     * @param string $msg       - Message.
     * @param int    $exitCode  - Exit Code.
     * @param array  $output    - Output.
     * @param string $cmd       - Command executed.
     * @param array  $arguments - Command arguments.
     *
     * @return self
     */
    public static function create(
        string $msg,
        int $exitCode,
        array $output,
        string $cmd, array
        $arguments = []
    ) : self {
        $e = new self($msg, $exitCode);

        $e->setOutput($output)
            ->setCmd($cmd)
            ->setArguments($arguments);

        return $e;
    }
}