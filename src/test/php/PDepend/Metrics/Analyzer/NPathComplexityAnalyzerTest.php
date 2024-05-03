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

namespace PDepend\Metrics\Analyzer;

use PDepend\Metrics\AbstractMetricsTestCase;
use PDepend\Source\AST\AbstractASTCallable;
use PDepend\Util\Cache\Driver\MemoryCacheDriver;

/**
 * Test case for the NPath complexity analyzer.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 *
 * @covers \PDepend\Metrics\AbstractCachingAnalyzer
 * @covers \PDepend\Metrics\Analyzer\NPathComplexityAnalyzer
 * @group unittest
 */
class NPathComplexityAnalyzerTest extends AbstractMetricsTestCase
{
    /**
     * @var \PDepend\Util\Cache\CacheDriver
     * @since 1.0.0
     */
    private $cache;

    /**
     * Initializes a in memory cache.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new MemoryCacheDriver();
    }

    /**
     * testAnalyzerRestoresExpectedFunctionMetricsFromCache
     *
     * @return void
     * @since 1.0.0
     */
    public function testAnalyzerRestoresExpectedFunctionMetricsFromCache(): void
    {
        $namespaces = $this->parseCodeResourceForTest();
        $function = $namespaces->current()
            ->getFunctions()
            ->current();

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($namespaces);

        $metrics0 = $analyzer->getNodeMetrics($function);

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($namespaces);

        $metrics1 = $analyzer->getNodeMetrics($function);

        $this->assertEquals($metrics0, $metrics1);
    }

    /**
     * testAnalyzerRestoresExpectedMethodMetricsFromCache
     *
     * @return void
     * @since 1.0.0
     */
    public function testAnalyzerRestoresExpectedMethodMetricsFromCache(): void
    {
        $namespaces = $this->parseCodeResourceForTest();
        $method   = $namespaces->current()
            ->getClasses()
            ->current()
            ->getMethods()
            ->current();

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($namespaces);

        $metrics0 = $analyzer->getNodeMetrics($method);

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($namespaces);

        $metrics1 = $analyzer->getNodeMetrics($method);

        $this->assertEquals($metrics0, $metrics1);
    }

    /**
     * testNPathComplexityForNestedIfStatementsWithScope
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForNestedIfStatementsWithScope(): void
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * testNPathComplexityForNestedIfStatementsWithoutScope
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForNestedIfStatementsWithoutScope(): void
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * testNPathComplexityForSiblingConditionalExpressions
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForSiblingConditionalExpressions(): void
    {
        $this->assertEquals(4, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForSiblingExpressions
     *
     * @return void
     * @since 0.9.12
     * @todo What happens with boolean/logical expressions within the body of
     *       any other statement/expression?
     */
    public function testNPathComplexityForSiblingExpressions(): void
    {
        $this->assertEquals(6, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForTwoSiblingIfStatetements
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForTwoSiblingIfStatetements(): void
    {
        $this->assertEquals(4, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForForeachStatementWithNestedIfStatetements
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForForeachStatementWithNestedIfStatetements(): void
    {
        $this->assertEquals(3, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForSiblingIfStatementsAndForeachStatement
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForSiblingIfStatementsAndForeachStatement(): void
    {
        $this->assertEquals(12, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForComplexFunction
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForComplexFunction(): void
    {
        $this->assertEquals(24, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForConditionalsInArrayDeclaration
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForComplexNestedControlStatements(): void
    {
        $this->assertEquals(63, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForConditionalsInArrayDeclaration
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForConditionalsInArrayDeclaration(): void
    {
        $this->assertEquals(16, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityIsZeroForEmptyMethod
     *
     * @return void
     */
    public function testNPathComplexityIsZeroForEmptyMethod(): void
    {
        $this->assertEquals(1, $this->calculateMethodMetric());
    }

    /**
     * Tests a method body with a simple if statement.
     *
     * @return void
     */
    public function testNPathComplexityForMethodWithSimpleIfStatement(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests a method body with a simple if statement with dynamic identifier.
     *
     * @return void
     */
    public function testNPathComplexityForIfStatementWithNestedDynamicIdentifier(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against consecutive if-statements.
     *
     * @return void
     */
    public function testNPathComplexityForConsecutiveIfStatements(): void
    {
        $this->assertEquals(80, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against multiple if-else-if statements.
     *
     * @return void
     */
    public function testNPathComplexityForConsecutiveIfElseIfStatements(): void
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against multiple if-elseif statements.
     *
     * @return void
     */
    public function testNPathComplexityForConsecutiveIfElsifStatements(): void
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against an empty while statement.
     *
     * @return void
     */
    public function testNPathComplexityForEmptyWhileStatement(): void
    {
        $this->assertEquals(3, $this->calculateMethodMetric());
    }

    /**
     * Tests the anaylzer with nested while statements.
     *
     * @return void
     */
    public function testNPathComplexityForNestedWhileStatements(): void
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the npath algorithm with a simple do-while statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleDoWhileStatement(): void
    {
        $this->assertEquals(3, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer with a simple for statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleForStatement(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer with a complex for statement.
     *
     * @return void
     */
    public function testNPathComplexityForComplexForStatement(): void
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation with a simple foreach statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleForeachStatement(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a simple return statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleReturnStatement(): void
    {
        $this->assertEquals(1, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a return statement that contains boolean expressions.
     *
     * @return void
     */
    public function testNPathComplexityForReturnStatementWithBooleanExpressions(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a return statement that contains a conditional.
     *
     * @return void
     */
    public function testNPathComplexityForReturnStatementWithConditionalStatement(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a simple switch statement that contains one case
     * child.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleSwitchStatement(): void
    {
        $this->assertEquals(1, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a switch statement that contains multiple case
     * statements.
     *
     * @return void
     */
    public function testNPathComplexityForSwitchStatementWithMultipleCaseStatements(): void
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a switch statement that contains complex case
     * statements.
     *
     * @return void
     */
    public function testNPathComplexityForSwitchStatementWithComplexCaseStatements(): void
    {
        $this->assertEquals(8, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a simple try statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleTryCatchStatement(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a try statement with multiple catch statements.
     *
     * @return void
     */
    public function testNPathComplexityForTryStatementWithMutlipleCatchStatements(): void
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a try statement with nested if statements.
     *
     * @return void
     */
    public function testNPathComplexityForTryCatchStatementWithNestedIfStatements(): void
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a conditional statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleConditionalStatement(): void
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with nested conditional statements.
     *
     * @return void
     */
    public function testNPathComplexityForTwoNestedConditionalStatements(): void
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with nested conditional statements.
     *
     * @return void
     */
    public function testNPathComplexityForThreeNestedConditionalStatements(): void
    {
        $this->assertEquals(6, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a conditional statement with boolean/logical
     * expressions.
     *
     * @return void
     */
    public function testNPathComplexityForConditionalStatementWithLogicalExpressions(): void
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm implementation against an existing bug in the
     * combination of return, conditional and if statement.
     *
     * @return void
     */
    public function testNPathComplexityForReturnStatementWithConditional(): void
    {
        $npath = $this->calculateMethodMetric();
        $this->assertEquals(3, $npath);
    }

    /**
     * Returns the NPath Complexity of the first function found in source file
     * associated with the calling test case.
     *
     * @return integer
     * @since 0.9.12
     */
    private function calculateFunctionMetric()
    {
        return $this->calculateNPathComplexity(
            $this->getFirstFunctionForTestCaseInternal()
        );
    }

    /**
     * Parses the source code associated with the calling test case and returns
     * the first function found in the test case source file.
     *
     * @return \PDepend\Source\AST\ASTFunction
     * @since 0.9.12
     */
    private function getFirstFunctionForTestCaseInternal()
    {
        return $this->parseTestCaseSource($this->getCallingTestMethod())
            ->current()
            ->getFunctions()
            ->current();
    }

    /**
     * Returns the NPath Complexity of the first method found in source file
     * associated with the calling test case.
     *
     * @return integer
     * @since 0.9.12
     */
    private function calculateMethodMetric()
    {
        return $this->calculateNPathComplexity(
            $this->getFirstMethodForTestCaseInternal()
        );
    }

    /**
     * Parses the source code associated with the calling test case and returns
     * the first method found in the test case source file.
     *
     * @return \PDepend\Source\AST\ASTMethod
     * @since 0.9.12
     */
    private function getFirstMethodForTestCaseInternal()
    {
        return $this->parseTestCaseSource($this->getCallingTestMethod())
            ->current()
            ->getClasses()
            ->current()
            ->getMethods()
            ->current();
    }

    /**
     * Calculates the NPath complexity for the given callable instance.
     *
     * @param \PDepend\Source\AST\AbstractASTCallable $callable
     * @return string
     * @since 0.9.12
     */
    private function calculateNPathComplexity(AbstractASTCallable $callable)
    {
        $analyzer = $this->createAnalyzer();
        $callable->accept($analyzer);

        $metrics = $analyzer->getNodeMetrics($callable);
        return $metrics['npath'];
    }

    /**
     * Creates a ready to use npath complexity analyzer.
     *
     * @return \PDepend\Metrics\Analyzer\NPathComplexityAnalyzer
     * @since 1.0.0
     */
    private function createAnalyzer()
    {
        $analyzer = new NPathComplexityAnalyzer();
        $analyzer->setCache($this->cache);

        return $analyzer;
    }
}
