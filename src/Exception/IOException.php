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
class IOException extends BaseException
{
    /**
     * Creates an instance of this exception.
     *
     * @param string $msg - Exception message.
     *
     * @return self
     */
    public static function create(string $msg) : self
    {
        return new self($msg);
    }
}