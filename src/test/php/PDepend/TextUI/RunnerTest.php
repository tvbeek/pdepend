<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2017 Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\TextUI;

use PDepend\AbstractTestCase;
use PDepend\Input\ExtensionFilter;
use PDepend\Input\Filter;
use PDepend\Report\ReportGeneratorFactory;
use PDepend\Source\AST\ASTArtifactList\PackageArtifactFilter;
use Symfony\Component\DependencyInjection\Container;

/**
 * Test case for the text ui runner.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @covers \PDepend\TextUI\Runner
 * @group unittest
 */
class RunnerTest extends AbstractTestCase
{
    /**
     * Tests that the runner exits with an exception for an invalud source
     * directory.
     *
     * @return void
     */
    public function testRunnerThrowsRuntimeExceptionForInvalidSourceDirectory(): void
    {
        $this->expectException(\RuntimeException::class);

        $runner = $this->createTextUiRunner();
        $runner->setSourceArguments(['foo/bar']);
        $runner->run();
    }

    /**
     * Tests that the runner stops processing if no logger is specified.
     *
     * @return void
     */
    public function testRunnerThrowsRuntimeExceptionIfNoLoggerIsSpecified(): void
    {
        $this->expectException(\RuntimeException::class);

        $runner = $this->createTextUiRunner();
        $runner->setSourceArguments([$this->createCodeResourceUriForTest()]);
        $runner->run();
    }

    /**
     * testRunnerUsesCorrectFileFilter
     *
     * @return void
     */
    public function testRunnerUsesCorrectFileFilter(): void
    {
        $expected = [
            'pdepend.test'  =>  [
                'functions'   =>  ['foo'],
                'classes'     =>  ['MyException'],
                'interfaces'  =>  [],
                'exceptions'  =>  []
            ],
            'pdepend.test2'  =>  [
                'functions'   =>  [],
                'classes'     =>  ['YourException'],
                'interfaces'  =>  [],
                'exceptions'  =>  []
            ]
        ];

        $runner = $this->createTextUiRunner();
        $runner->setWithoutAnnotations();
        $runner->setFileExtensions(['inc']);

        $actual = $this->runRunnerAndReturnStatistics(
            $runner,
            $this->createCodeResourceUriForTest()
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testSetExcludeDirectories(): void
    {
        /** @var Filter[] $record */
        $record = [];
        $engine = $this->getMockBuilder('PDepend\\Engine')
            ->disableOriginalConstructor()
            ->getMock();
        $engine->expects($this->exactly(2))
            ->method('addFileFilter')
            ->willReturnCallback(function (Filter $excludePathFilter) use (&$record): void {
                $record[] = $excludePathFilter;
            });
        $engine->expects($this->exactly(0))
            ->method('setCodeFilter');
        $container = new Container();

        $runner = new Runner(new ReportGeneratorFactory($container), $engine);
        $runner->setExcludeDirectories([dirname(__DIR__)]);

        try {
            $this->silentRun($runner);
        } catch (\Exception $exception) {
            // noop
        }

        $this->assertCount(2, $record);
        $this->assertInstanceOf('PDepend\\Input\\ExtensionFilter', $record[0]);
        $this->assertInstanceOf('PDepend\\Input\\ExcludePathFilter', $record[1]);
    }

    /**
     * @return void
     */
    public function testSetExcludeNamespaces(): void
    {
        /** @var object[] $record */
        $record = [];
        $engine = $this->getMockBuilder('PDepend\\Engine')
            ->disableOriginalConstructor()
            ->getMock();
        $engine->expects($this->exactly(2))
            ->method('addFileFilter')
            ->willReturnCallback(function (Filter $excludePathFilter) use (&$record): void {
                $record[] = $excludePathFilter;
            });
        $engine->expects($this->once())
            ->method('setCodeFilter')
            ->willReturnCallback(function (PackageArtifactFilter $excludePathFilter) use (&$record): void {
                $record[] = $excludePathFilter;
            });
        $container = new Container();

        $runner = new Runner(new ReportGeneratorFactory($container), $engine);
        $runner->setExcludeNamespaces(['PDepend']);

        try {
            $this->silentRun($runner);
        } catch (\Exception $exception) {
            // noop
        }

        $this->assertCount(3, $record);
        $this->assertInstanceOf('PDepend\\Input\\ExtensionFilter', $record[0]);
        $this->assertInstanceOf('PDepend\\Input\\ExcludePathFilter', $record[1]);
        $this->assertInstanceOf('PDepend\\Source\\AST\\ASTArtifactList\\PackageArtifactFilter', $record[2]);
    }

    /**
     * Tests that the runner handles the <b>--without-annotations</b> option
     * correct.
     *
     * @return void
     */
    public function testRunnerHandlesWithoutAnnotationsOptionCorrect(): void
    {
        $expected = [
            'pdepend.test'  =>  [
                'functions'   =>  ['foo'],
                'classes'     =>  ['MyException'],
                'interfaces'  =>  [],
                'exceptions'  =>  []
            ],
            'pdepend.test2'  =>  [
                'functions'   =>  [],
                'classes'     =>  ['YourException'],
                'interfaces'  =>  [],
                'exceptions'  =>  []
            ]
        ];

        $runner = $this->createTextUiRunner();
        $runner->setWithoutAnnotations();

        $actual = $this->runRunnerAndReturnStatistics(
            $runner,
            $this->createCodeResourceUriForTest()
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * testSupportBadDocumentation
     *
     * @return void
     */
    public function testSupportBadDocumentation(): void
    {
        $expected = [
            '+global'  =>  [
                'functions'   =>  ['pkg3_foo'],
                'classes'     =>  [
                    'Bar',
                    'pkg1Bar',
                    'pkg1Barfoo',
                    'pkg1Foo',
                    'pkg1Foobar',
                    'pkg2Bar',
                    'pkg2Barfoo',
                    'pkg2Foobar',
                ],
                'interfaces'  =>  [
                    'pkg1FooI',
                    'pkg2FooI',
                    'pkg3FooI'
                ],
                'exceptions'  =>  []
            ]
        ];

        $runner = $this->createTextUiRunner();
        $actual = $this->runRunnerAndReturnStatistics(
            $runner,
            $this->createCodeResourceUriForTest()
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * testRunnerHasParseErrorsReturnsFalseForValidSource
     *
     * @return void
     */
    public function testRunnerHasParseErrorsReturnsFalseForValidSource(): void
    {
        $runner = $this->createTextUiRunner();
        $runner->addReportGenerator('dummy-logger', $this->createRunResourceURI());
        $runner->setSourceArguments([$this->createCodeResourceUriForTest()]);

        $this->silentRun($runner);

        $this->assertFalse($runner->hasParseErrors());
    }

    /**
     * testRunnerHasParseErrorsReturnsTrueForInvalidSource
     *
     * @return void
     */
    public function testRunnerHasParseErrorsReturnsTrueForInvalidSource(): void
    {
        $runner = $this->createTextUiRunner();
        $runner->addReportGenerator('dummy-logger', $this->createRunResourceURI());
        $runner->setSourceArguments([$this->createCodeResourceUriForTest()]);

        $this->silentRun($runner);

        $this->assertTrue($runner->hasParseErrors());
    }

    /**
     * testRunnerGetParseErrorsReturnsArrayWithParsingExceptionMessages
     *
     * @return void
     */
    public function testRunnerGetParseErrorsReturnsArrayWithParsingExceptionMessages(): void
    {
        $runner = $this->createTextUiRunner();
        $runner->addReportGenerator('dummy-logger', $this->createRunResourceURI());
        $runner->setSourceArguments([$this->createCodeResourceUriForTest()]);

        ob_start();
        $runner->run();
        ob_end_clean();

        $errors = $runner->getParseErrors();
        $this->assertStringContainsString('Unexpected token: }, line: 10, col: 1, file: ', $errors[0]);
    }

    /**
     * testRunnerThrowsExceptionForUndefinedLoggerClass
     *
     * @return void
     */
    public function testRunnerThrowsExceptionForUndefinedLoggerClass(): void
    {
        $this->expectException(\RuntimeException::class);

        $runner = $this->createTextUiRunner();
        $runner->addReportGenerator('FooBarLogger', $this->createRunResourceURI());
        $runner->run();
    }

    /**
     * Executes the runner class and returns an array with namespace statistics.
     *
     * @param \PDepend\TextUI\Runner $runner The runner instance.
     * @param $pathName The source path.
     * @return array
     */
    private function runRunnerAndReturnStatistics(Runner $runner, $pathName)
    {
        $logFile = $this->createRunResourceURI();

        $runner->setSourceArguments([$pathName]);
        $runner->addReportGenerator('dummy-logger', $logFile);

        $this->silentRun($runner);

        $data = unserialize(file_get_contents($logFile));
        $code = $data['code'];

        $actual = [];
        foreach ($code as $namespace) {
            $statistics = [
                'functions'   =>  [],
                'classes'     =>  [],
                'interfaces'  =>  [],
                'exceptions'  =>  []
            ];
            foreach ($namespace->getFunctions() as $function) {
                $statistics['functions'][] = $function->getName();
                foreach ($function->getExceptionClasses() as $exception) {
                    $statistics['exceptions'][] = $exception->getName();
                }
            }

            foreach ($namespace->getClasses() as $class) {
                $statistics['classes'][] = $class->getName();
            }

            foreach ($namespace->getInterfaces() as $interface) {
                $statistics['interfaces'][] = $interface->getName();
            }

            sort($statistics['functions']);
            sort($statistics['classes']);
            sort($statistics['interfaces']);
            sort($statistics['exceptions']);

            $actual[$namespace->getName()] = $statistics;
        }
        ksort($actual);

        return $actual;
    }
}
