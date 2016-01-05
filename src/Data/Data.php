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
class Data implements DataInterface
{
    use DataTrait;

    /**
     * Constructor.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     */
    public function __construct(array $data = [], array $options = [])
    {
        $this->setOptions($options)
            ->setData($data);
    }


    /**
     * Returns default options.
     *
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return [];
    }


}