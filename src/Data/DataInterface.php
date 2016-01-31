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


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
interface DataInterface
{
    /**
     * Method setReadOnly.
     *
     * @param bool $bool - Read Only?
     *
     * @return self
     */
    public function setReadOnly(bool $bool);

    /**
     * Is this instance read only?
     *
     * @return bool
     */
    public function isReadOnly(): bool;

    /**
     * Calls array_replace_recursive using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return self
     */
    public function replaceRecursive(string $index, array $value, $default = null, array $options = []);

    /**
     * Calls array_merge_recursive using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return self
     */
    public function mergeRecursive(string $index, array $value, $default = null, array $options = []);

    /**
     * Calls array_replace using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return self
     */
    public function replace(string $index, array $value, $default = null, array $options = []);

    /**
     * Calls array_merge using the data existent on $index and data on $value.
     *
     * @param string $index   - Index.
     * @param array  $value   - Value.
     * @param mixed  $default - Default value.
     * @param array  $options - Options.
     *
     * @return self
     */
    public function merge(string $index, array $value, $default = null, array $options = []);

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
     * @return self
     */
    public function callFunction(string $function, string $index, array $value, $default = null, array $options = []);

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
    public function get(string $index, $default = null, array $options = []);

    /**
     * Returns true if the parameter exists or false otherwise.
     *
     * @param string $index   - Index to search for.
     * @param array  $options - Options.
     *
     * @return bool
     */
    public function has(string $index, array $options = []): bool;

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
     * @return self
     */
    public function set(string $index, $value, array $options = []);

    /**
     * Removes an element from the configuration. It allows to remove elements recursively. For example,
     * if you remove the key "user.email", the result is similar to the following:
     *
     * unset($data['user']['email']);
     *
     * @param string $index   - Parameter index.
     * @param mixed  $value   - Parameter value.
     * @param array  $options - Options.
     *
     * @return self
     */
    public function remove(string $index, array $options = []);

    /**
     * Getter method for field _data.
     *
     * @return array
     */
    public function getData();

    /**
     * Setter method for field data.
     *
     * @param array $data                     - data.
     * @param bool  $replaceTemplateVariables - Replace Template Variables?
     *
     * @return self
     */
    public function setData(array $data, $replaceTemplateVariables = true);
}