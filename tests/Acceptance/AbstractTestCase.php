<?php
/*
 * This file is part of the common-utils package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\CommonUtils\Test\Acceptance;

use PHPUnit\Framework\TestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Returns the path to the "tmp" dir used for testing purposes.
     *
     * @return string
     */
    protected function getTmpDir()
    {
        $dir = realpath(__DIR__).'/../tmp';

        if (!$dir) {
            throw new \RuntimeException('Couldn\'t determine path to "tmp" dir for testing!');
        }

        return $dir;
    }

    /**
     * Cleans up temp files.
     *
     * @return void
     */
    protected function cleanUp()
    {
        $this->rm($this->getTmpDir());
    }

    /**
     * Deletes everything found inside a directory.
     *
     * @param string $dir - Directory.
     *
     * @return void
     */
    protected function rm($dir)
    {
        foreach (glob($dir.'/*') as $fileOrDir) {
            if (is_link($fileOrDir) || is_file($fileOrDir)) {
                unlink($fileOrDir);

                continue;
            }

            $this->rm($fileOrDir);

            rmdir($fileOrDir);
        }
    }
}