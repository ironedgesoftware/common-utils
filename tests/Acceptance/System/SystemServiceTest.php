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

use IronEdge\Component\CommonUtils\Exception\CantRemoveDirectoryException;
use IronEdge\Component\CommonUtils\Exception\CommandException;
use IronEdge\Component\CommonUtils\Exception\IOException;
use IronEdge\Component\CommonUtils\Exception\NotADirectoryException;
use IronEdge\Component\CommonUtils\System\SystemService;
use IronEdge\Component\CommonUtils\Test\Acceptance\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class SystemServiceTest extends AbstractTestCase
{
    public function setUp()
    {
        $this->cleanUp();
    }

    public function tearDown()
    {
        $this->cleanUp();
    }



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

    public function test_mkdir_shouldCreateMissingDirectories()
    {
        $tmpDir = $this->getTmpDir();
        $createDir = $tmpDir.'/test/dir';
        $systemService = $this->createInstance();

        $this->assertFalse(is_dir($createDir));

        $systemService->mkdir($createDir);

        $this->assertTrue(is_dir($createDir));

        $systemService->mkdir($createDir);

        // It shouldn't throw an error if directory already exists.

        $this->assertTrue(is_dir($createDir));

        $systemService->mkdir($createDir);
    }

    public function test_mkdir_throwExceptionIfPathExistsAndIsAFile()
    {
        $tmpDir = $this->getTmpDir();
        $file = $tmpDir.'/test';

        $this->setExpectedExceptionRegExp(
            get_class(new NotADirectoryException()),
            '#Can\\\'t create directory \"'.preg_quote($file).
            '\" because it exists and it\\\'s not a directory\.#'
        );

        touch($file);

        $systemService = $this->createInstance();

        $systemService->mkdir($file);
    }

    public function test_mkdir_invalidContextUsageShouldThrowAnException()
    {
        $tmpDir = $this->getTmpDir();
        $file = $tmpDir.'/test';

        $this->setExpectedExceptionRegExp(
            get_class(new \TypeError()),
            '#mkdir\(\) expects parameter 4 to be resource, string given#'
        );

        $systemService = $this->createInstance();

        $systemService->mkdir($file, ['context' => 'invalidContext']);
    }

    public function test_rm_shouldRemoveFiles()
    {
        $file = $this->getTmpDir().'/test_file';
        $systemService = $this->createInstance();

        $this->assertFalse(is_file($file));

        // It shouldn't throw an exception if file does not exist.

        $systemService->rm($file);

        touch($file);

        $this->assertTrue(is_file($file));

        $systemService->rm($file);

        $this->assertFalse(is_file($file));
    }

    public function test_rm_ifItsADirectoryAndForceOptionIsFalseThenThrowException()
    {
        $dir = $this->getTmpDir().'/test';

        $this->setExpectedExceptionRegExp(
            get_class(new CantRemoveDirectoryException()),
            '#\"'.preg_quote($dir).'\" is a directory\. If you really want to remove it, ' .
            'set the \"force\" option to \"true\"\.#'
        );

        mkdir($dir);

        $systemService = $this->createInstance();

        $systemService->rm($dir);
    }

    public function test_rm_ifItsADirectoryAndItsNotEmptyAndRecursiveOptionIsFalseThenThrowException()
    {
        $dir = $this->getTmpDir().'/test/dir';

        $this->setExpectedExceptionRegExp(
            get_class(new IOException()),
            '#Couldn\\\'t remove directory \"'.preg_quote(dirname($dir)).'\"\. Last PHP Error\:#'
        );

        mkdir($dir, 0777, true);

        $systemService = $this->createInstance();

        $systemService->rm($this->getTmpDir().'/test', ['force' => true]);
    }

    public function test_rm_ifDirectoryContainsFilesAndOtherDirsThenItShouldRemoveThemAll()
    {
        $testDir = $this->getTmpDir();
        $dirs = [
            $testDir.'/test',
            $testDir.'/test2/other',
            $testDir.'/test3/a/b/c'
        ];
        $files = [
            $dirs[0].'/abc',
            $dirs[1].'/def',
            $dirs[2].'/xyz'
        ];

        foreach ($dirs as $d) {
            mkdir($d, 0777, true);
        }

        foreach ($files as $f) {
            touch($f);
        }

        $systemService = $this->createInstance();

        foreach ($dirs as $d) {
            $systemService->rm($d, ['force' => true, 'recursive' => true]);
        }

        foreach ($dirs as $d) {
            $this->assertFalse(is_dir($d));
        }
    }

    public function test_rm_ifContextIsInvalidThenThrowException()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new \TypeError()),
            '#unlink\(\) expects parameter 2 to be resource, string given#'
        );

        $file = $this->getTmpDir().'/test_file';

        touch($file);

        $systemService = $this->createInstance();

        $systemService->rm($file, ['context' => 'InvalidContext']);
    }

    public function test_scandir_ifASymlinkIsReceivedButItsBrokenThenThrowException()
    {
        $testSymlink = $this->getTmpDir().'/a';
        $badTarget = '/asdasd/ASdasdas7dasdas/Das';
        @symlink($badTarget, $testSymlink);

        $this->setExpectedExceptionRegExp(
            get_class(new IOException()),
            '#Couldn\\\'t scan directory "'.$badTarget.'"\. Last PHP Error#'
        );

        try {
            $this->createInstance()->scandir($testSymlink);
        } catch (\Exception $e) {
            unlink($testSymlink);

            throw $e;
        }
    }

    public function test_scandir_ifRecursiveOptionIsTrueThenTraverseDirectories()
    {
        $testDir = $this->getTmpDir();
        $dirs = [
            $testDir.'/test',
            $testDir.'/test2/other',
            $testDir.'/test3/a/b/c'
        ];
        $files = [
            $dirs[0].'/abc',
            $dirs[1].'/def',
            $dirs[2].'/xyz'
        ];

        foreach ($dirs as $d) {
            mkdir($d, 0777, true);
        }

        foreach ($files as $f) {
            touch($f);
        }

        $systemService = $this->createInstance();

        $result = $systemService->scandir($testDir, ['recursive' => true, 'skipDots' => true]);

        $this->assertCount(11, $result, print_r($result, true));
    }

    public function test_scandir_ifSkipSymlinksIsTrueThenDontReturnSymlinks()
    {
        $testDir = $this->getTmpDir();
        $dirs = [
            $testDir.'/test/test2'
        ];
        $symlinks = [
            $testDir.'/test/a' => $testDir.'/test/test2'
        ];

        foreach ($dirs as $d) {
            mkdir($d, 0777, true);
        }

        foreach ($symlinks as $source => $target) {
            symlink($target, $source);
        }

        $result = $this->createInstance()->scandir(
            $testDir.'/test',
            ['skipDots' => false, 'skipSymlinks' => true, 'recursive' => false]
        );

        $this->assertCount(3, $result);

        foreach ($result as $i => $e) {
            $result[$i] = basename($e);
        }

        $this->assertTrue(in_array('.', $result));
        $this->assertTrue(in_array('..', $result));
        $this->assertTrue(in_array('test2', $result));
    }

    public function test_scandir_ifContextIsInvalidThenThrowException()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new \TypeError())
        );

        $this->createInstance()->scandir($this->getTmpDir(), ['context' => 'invalidContext']);
    }

    public function test_mkdir_throwExceptionIfFunctionReturnFalse()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new IOException())
        );

        $this->createInstance()->mkdir('http://127.0.0.1');
    }

    public function test_rmdir_throwExceptionIfFunctionReturnFalse()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new IOException())
        );

        $this->createInstance()->rm('http://127.0.0.1', ['skipIfAlreadyRemoved' => false]);
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