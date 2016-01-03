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

namespace IronEdge\Component\CommonUtils\Options;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
interface OptionsInterface
{
    /**
     * Returns the value of field _options.
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Sets the value of field options.
     *
     * @param array $options - options.
     *
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Sets an option's value.
     *
     * @param string $name  - Option name.
     * @param mixed  $value - Option value.
     *
     * @return $this
     */
    public function setOption(string $name, $value);

    /**
     * Returns an option's value, or $default if option $name does not exist.
     *
     * @param string $name    - Option name.
     * @param mixed  $default - Default value.
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * Returns true if this graph has an option $name.
     *
     * @param string $name - Option name.
     *
     * @return bool
     */
    public function hasOption(string $name): bool;

    /**
     * Returns the default options applied to this graph.
     *
     * @return array
     */
    public function getDefaultOptions(): array;
}