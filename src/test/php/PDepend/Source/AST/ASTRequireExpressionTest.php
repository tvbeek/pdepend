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
 * @since 0.9.12
 */

namespace PDepend\Source\AST;

/**
 * Test case for the {@link \PDepend\Source\AST\ASTRequireExpression} class.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.12
 *
 * @covers \PDepend\Source\Language\PHP\AbstractPHPParser
 * @covers \PDepend\Source\AST\ASTRequireExpression
 * @group unittest
 */
class ASTRequireExpressionTest extends ASTNodeTestCase
{
    /**
     * testIsOnceReturnsFalseByDefault
     *
     * @return void
     */
    public function testIsOnceReturnsFalseByDefault(): void
    {
        $expr = new \PDepend\Source\AST\ASTRequireExpression();
        $this->assertFalse($expr->isOnce());
    }

    /**
     * testIsOnceReturnsTrueForRequireOnceExpression
     *
     * @return void
     */
    public function testIsOnceReturnsTrueForRequireOnceExpression(): void
    {
        $expr = $this->getFirstRequireExpressionInFunction();
        $this->assertTrue($expr->isOnce());
    }

    /**
     * testMagicSleepReturnsExpectedSetOfPropertyNames
     *
     * @return void
     */
    public function testMagicSleepReturnsExpectedSetOfPropertyNames(): void
    {
        $expr = new \PDepend\Source\AST\ASTRequireExpression();
        $this->assertEquals(
            [
                'once',
                'comment',
                'metadata',
                'nodes'
            ],
            $expr->__sleep()
        );
    }

    /**
     * testRequireExpression
     *
     * @return \PDepend\Source\AST\ASTRequireExpression
     * @since 1.0.2
     */
    public function testRequireExpression()
    {
        $expr = $this->getFirstRequireExpressionInFunction();
        $this->assertInstanceOf('PDepend\\Source\\AST\\ASTRequireExpression', $expr);

        return $expr;
    }

    /**
     * testRequireExpressionHasExpectedStartLine
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpression
     */
    public function testRequireExpressionHasExpectedStartLine($expr): void
    {
        $this->assertEquals(4, $expr->getStartLine());
    }

    /**
     * testRequireExpressionHasExpectedStartColumn
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpression
     */
    public function testRequireExpressionHasExpectedStartColumn($expr): void
    {
        $this->assertEquals(5, $expr->getStartColumn());
    }

    /**
     * testRequireExpressionHasExpectedEndLine
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpression
     */
    public function testRequireExpressionHasExpectedEndLine($expr): void
    {
        $this->assertEquals(4, $expr->getEndLine());
    }

    /**
     * testRequireExpressionHasExpectedEndColumn
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpression
     */
    public function testRequireExpressionHasExpectedEndColumn($expr): void
    {
        $this->assertEquals(35, $expr->getEndColumn());
    }

    /**
     * testRequireExpressionWithParenthesis
     *
     * @return \PDepend\Source\AST\ASTRequireExpression
     * @since 1.0.2
     */
    public function testRequireExpressionWithParenthesis()
    {
        $expr = $this->getFirstRequireExpressionInFunction();
        $this->assertInstanceOf('PDepend\\Source\\AST\\ASTRequireExpression', $expr);

        return $expr;
    }

    /**
     * testRequireExpressionWithParenthesisHasExpectedStartLine
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpressionWithParenthesis
     */
    public function testRequireExpressionWithParenthesisHasExpectedStartLine($expr): void
    {
        $this->assertEquals(4, $expr->getStartLine());
    }

    /**
     * testRequireExpressionWithParenthesisHasExpectedStartColumn
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpressionWithParenthesis
     */
    public function testRequireExpressionWithParenthesisHasExpectedStartColumn($expr): void
    {
        $this->assertEquals(5, $expr->getStartColumn());
    }

    /**
     * testRequireExpressionWithParenthesisHasExpectedEndLine
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpressionWithParenthesis
     */
    public function testRequireExpressionWithParenthesisHasExpectedEndLine($expr): void
    {
        $this->assertEquals(6, $expr->getEndLine());
    }

    /**
     * testRequireExpressionWithParenthesisHasExpectedEndColumn
     *
     * @param \PDepend\Source\AST\ASTRequireExpression $expr
     *
     * @return void
     * @depends testRequireExpressionWithParenthesis
     */
    public function testRequireExpressionWithParenthesisHasExpectedEndColumn($expr): void
    {
        $this->assertEquals(5, $expr->getEndColumn());
    }

    /**
     * Returns a node instance for the currently executed test case.
     *
     * @return \PDepend\Source\AST\ASTRequireExpression
     */
    private function getFirstRequireExpressionInFunction()
    {
        return $this->getFirstNodeOfTypeInFunction(
            $this->getCallingTestMethod(),
            'PDepend\\Source\\AST\\ASTRequireExpression'
        );
    }
}
