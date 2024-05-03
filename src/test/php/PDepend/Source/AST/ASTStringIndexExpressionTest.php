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
 * Test case for the {@link \PDepend\Source\AST\ASTStringIndexExpression} class.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.12
 *
 * @covers \PDepend\Source\Language\PHP\AbstractPHPParser
 * @covers \PDepend\Source\AST\ASTIndexExpression
 * @covers \PDepend\Source\AST\ASTStringIndexExpression
 * @group unittest
 */
class ASTStringIndexExpressionTest extends ASTNodeTestCase
{
    /**
     * testStringIndexExpression
     *
     * @return \PDepend\Source\AST\ASTStringIndexExpression
     * @since 1.0.2
     */
    public function testStringIndexExpression()
    {
        $expr = $this->getFirstStringIndexExpressionInFunction();
        $this->assertInstanceOf('PDepend\\Source\\AST\\ASTStringIndexExpression', $expr);

        return $expr;
    }

    /**
     * testStringIndexExpressionHasExpectedStartLine
     *
     * @param \PDepend\Source\AST\ASTStringIndexExpression $expr
     *
     * @return void
     * @depends testStringIndexExpression
     */
    public function testStringIndexExpressionHasExpectedStartLine($expr): void
    {
        $this->assertEquals(4, $expr->getStartLine());
    }

    /**
     * testStringIndexExpressionHasExpectedStartColumn
     *
     * @param \PDepend\Source\AST\ASTStringIndexExpression $expr
     *
     * @return void
     * @depends testStringIndexExpression
     */
    public function testStringIndexExpressionHasExpectedStartColumn($expr): void
    {
        $this->assertEquals(23, $expr->getStartColumn());
    }

    /**
     * testStringIndexExpressionHasExpectedEndLine
     *
     * @param \PDepend\Source\AST\ASTStringIndexExpression $expr
     *
     * @return void
     * @depends testStringIndexExpression
     */
    public function testStringIndexExpressionHasExpectedEndLine($expr): void
    {
        $this->assertEquals(4, $expr->getEndLine());
    }

    /**
     * testStringIndexExpressionHasExpectedEndColumn
     *
     * @param \PDepend\Source\AST\ASTStringIndexExpression $expr
     *
     * @return void
     * @depends testStringIndexExpression
     */
    public function testStringIndexExpressionHasExpectedEndColumn($expr): void
    {
        $this->assertEquals(28, $expr->getEndColumn());
    }

    /**
     * Returns a node instance for the currently executed test case.
     *
     * @return \PDepend\Source\AST\ASTStringIndexExpression
     */
    private function getFirstStringIndexExpressionInFunction()
    {
        return $this->getFirstNodeOfTypeInFunction(
            $this->getCallingTestMethod(),
            'PDepend\\Source\\AST\\ASTStringIndexExpression'
        );
    }
}
