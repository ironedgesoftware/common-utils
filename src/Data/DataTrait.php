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

namespace IronEdge\Component\CommonUtils\Data;

use IronEdge\Component\CommonUtils\Exception\DataIsReadOnlyException;
use IronEdge\Component\CommonUtils\Options\OptionsTrait;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
trait DataTrait
{
    use OptionsTrait;


    /**
     * Data Array.
     *
     * @var array
     */
    private $_data = [];

    /**
     * If this is true, then data can't be manipulated.
     *
     * @var bool
     */
    private $_readOnly = false;


    /**
     * Sets this instance as read only or not.
     *
     * @param bool $bool - True or false.
     *
     * @return self
     */
    public function setReadOnly(bool $bool)
    {
        $this->_readOnly = $bool;

        return $this;
    }

    /**
     * Is this instance read only?
     *
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->_readOnly;
    }

    /**
     * Setter method for field data.
     *
     * @param array $data                     - data.
     * @param bool  $replaceTemplateVariables - Replace template variables?
     *
     * @return $this
     */
    public function setData(array $data, $replaceTemplateVariables = true)
    {
        $this->assertDataIsWritable();

        if ($replaceTemplateVariables) {
            $data = $this->replaceTemplateVariables($data);
        }

        $this->_data = $data;

        return $this;
    }

    /**
     * Getter method for field _data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Replaces the data with the template variables configured on this instance.
     *
     * @param string|array $data - Data.
     *
     * @return string|array
     */
    public function replaceTemplateVariables($data)
    {
        $this->assertDataIsWritable();

        if ($templateVariables = $this->getOption('templateVariables', [])) {
            $templateVariableKeys = array_keys($templateVariables);

            if (is_string($data)) {
                $data = str_replace($templateVariableKeys, $templateVariables, $data);
            } else if (is_array($data)) {
                array_walk_recursive(
                    $data,
                    function(&$value, &$key, &$data) {
                        $value = str_replace($data['keys'], $data['values'], $value);
                    },
                    [
                        'keys'      => $templateVariableKeys,
                        'values'    => $templateVariables
                    ]
                );
            }
        }

        return $data;
    }

    /**
     * Returns an element of the configuration array. You can search for values recursively using
     * a dot (or the separator set on the "separator" option). For example: user.email would look
     * for the value in the array like this: $data['user']['email'].
     *
     * @param string $index   - Index to search for.
     * @param mixed  $default - Default.
     * @param array  $options - Options.
     *
     * @return mixed
     */
    public function get(string $index, $default = null, array $options = [])
    {
        $separator = isset($options['separator']) ?
            $options['separator'] :
            $this->getOption('separator', '.');
        $value = $this->getData();
        $keys = explode($separator, $index);

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                $value = $default;

                break;
            }

            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Returns true if the parameter exists or false otherwise.
     *
     * @param string $index   - Index to search for.
     * @param array  $options - Options.
     *
     * @return bool
     */
    public function has(string $index, array $options = []): bool
    {
        $separator = isset($options['separator']) ?
            $options['separator'] :
            $this->getOption('separator', '.');
        $value = $this->getData();
        $keys = explode($separator, $index);

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return false;
            }

            $value = $value[$key];
        }

        return true;
    }

    /**
     * Sets an element of the configuration. It allows to set elements recursively. For example,
     * if you set the key "user.email" with value "a@a.com", the result is similar to the following:
     *
     * $data['user']['email'] = 'a@a.com'
     *
     * If some key does not exist, we will create it for you.
     *
     * @param string $index   - Parameter index.
     * @param mixed  $value   - Parameter value.
     * @param array  $options - Options.
     *
     * @return $this
     */
    public function set(string $index, $value, array $options = [])
    {
        $this->assertDataIsWritable();

        $separator = isset($options['separator']) ?
            $options['separator'] :
            $this->getOption('separator', '.');
        $root = &$this->_data;
        $keys = explode($separator, $index);
        $count = count($keys);

        foreach ($keys as $i => $key) {
            if ($i === ($count - 1)) {
                $root[$key] = $value;

                break;
            }

            if (!is_array($root) || !array_key_exists($key, $root)) {
                $root[$key] = [];
            }

            $root = &$root[$key];
        }

        return $this;
    }

    /**
     * Removes an element from the configuration. It allows to remove elements recursively. For example,
     * if you remove the key "user.email", the result is similar to the following:
     *
     * unset($data['user']['email']);
     *
     * @param string $index   - Parameter index.
     * @param array  $options - Options.
     *
     * @return self
     */
    public function remove(string $index, array $options = [])
    {
        $this->assertDataIsWritable();

        $separator = isset($options['separator']) ?
            $options['separator'] :
            $this->getOption('separator', '.');
        $root = &$this->_data;
        $keys = explode($separator, $index);
        $targetKey = count($keys) - 1;

        if (!$targetKey) {
            unset($root[$index]);

            return $this;
        }

        foreach ($keys as $i => $key) {
            if ($i === $targetKey) {
                unset($root[$key]);

                break;
            }

            $root = &$root[$key];
        }

        return $this;
    }

    /**
     * Calls array_replace_recursive using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return $this
     */
    public function replaceRecursive(string $index, array $value, $default = null, array $options = [])
    {
        $this->assertDataIsWritable();

        return $this->callFunction('array_replace_recursive', $index, $value, $default, $options);
    }

    /**
     * Calls array_merge_recursive using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return $this
     */
    public function mergeRecursive(string $index, array $value, $default = null, array $options = [])
    {
        $this->assertDataIsWritable();

        return $this->callFunction('array_merge_recursive', $index, $value, $default, $options);
    }

    /**
     * Calls array_replace using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return $this
     */
    public function replace(string $index, array $value, $default = null, array $options = [])
    {
        $this->assertDataIsWritable();

        return $this->callFunction('array_replace', $index, $value, $default, $options);
    }

    /**
     * Calls array_merge using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return $this
     */
    public function merge(string $index, array $value, $default = null, array $options = [])
    {
        $this->assertDataIsWritable();

        return $this->callFunction('array_merge', $index, $value, $default, $options);
    }

    /**
     * Obtains data on $index, calls $function using as first parameter the data obtained, and as second
     * parameter $value.
     *
     * @param string $function - Function to call.
     * @param string $index    - Index.
     * @param array  $value    - Value.
     * @param mixed  $default  - Default value.
     * @param array  $options  - Options.
     *
     * @return $this
     */
    public function callFunction(
        string $function,
        string $index,
        array $value,
        $default = null,
        array $options = []
    ) {
        if (!function_exists($function)) {
            throw new \RuntimeException('Function "'.$function.'" does not exist!');
        }

        $data = $this->get($index, $default, $options);

        $value = $function($data, $value);

        $this->set($index, $value, $options);

        return $this;
    }

    /**
     * Throws an exception if this instance is read only.
     *
     * @throws DataIsReadOnlyException
     *
     * @return void
     */
    protected function assertDataIsWritable()
    {
        if ($this->isReadOnly()) {
            throw DataIsReadOnlyException::create();
        }
    }
}