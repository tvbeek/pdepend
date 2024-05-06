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

namespace PDepend\Source\AST;

use PDepend\AbstractTestCase;

/**
 * Test case for the code file class.
 *
 * @covers \PDepend\Source\AST\ASTCompilationUnit
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @group unittest
 */
class ASTCompilationUnitTest extends AbstractTestCase
{
    /**
     * testGetNameReturnsTheFileName
     */
    public function testGetNameReturnsTheFileName(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(__FILE__, $file->getName());
    }

    /**
     * testGetFileNameReturnsTheFileName
     */
    public function testGetFileNameReturnsTheFileName(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(__FILE__, $file->getFileName());
    }

    /**
     * testGetIdReturnsNullByDefault
     */
    public function testGetIdReturnsNullByDefault(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertNull($file->getId());
    }

    /**
     * testGetIdReturnsInjectedIdValue
     */
    public function testGetIdReturnsInjectedIdValue(): void
    {
        $compilationUnit = new ASTCompilationUnit(__FILE__);
        $compilationUnit->setId(__FUNCTION__);

        $this->assertEquals(__FUNCTION__, $compilationUnit->getId());
    }

    /**
     * testGetDocCommentReturnsNullByDefault
     */
    public function testGetDocCommentReturnsNullByDefault(): void
    {
        $file = new ASTCompilationUnit(null);
        $this->assertNull($file->getComment());
    }

    /**
     * testGetDocCommentReturnsInjectedDocCommentValue
     */
    public function testGetDocCommentReturnsInjectedDocCommentValue(): void
    {
        $file = new ASTCompilationUnit(null);
        $file->setComment('/** Manuel */');

        $this->assertEquals('/** Manuel */', $file->getComment());
    }

    /**
     * testGetTokensDelegatesCallToCacheRestoreWithFileId
     */
    public function testGetTokensDelegatesCallToCacheRestoreWithFileId(): void
    {
        $cache = $this->createCacheFixture();
        $cache->expects($this->once())
            ->method('type')
            ->with($this->equalTo('tokens'))
            ->will($this->returnValue($cache));
        $cache->expects($this->once())
            ->method('restore')
            ->with($this->equalTo(__FUNCTION__));

        $compilationUnit = new ASTCompilationUnit(null);
        $compilationUnit->setCache($cache);
        $compilationUnit->setId(__FUNCTION__);

        $compilationUnit->getTokens();
    }

    /**
     * testSetTokensDelegatesCallToCacheStoreWithFileId
     */
    public function testSetTokensDelegatesCallToCacheStoreWithFileId(): void
    {
        $cache = $this->createCacheFixture();
        $cache->expects($this->once())
            ->method('type')
            ->with($this->equalTo('tokens'))
            ->will($this->returnValue($cache));
        $cache->expects($this->once())
            ->method('store')
            ->with($this->equalTo(__FUNCTION__), $this->equalTo([1, 2, 3]));

        $compilationUnit = new ASTCompilationUnit(null);
        $compilationUnit->setCache($cache);
        $compilationUnit->setId(__FUNCTION__);

        $compilationUnit->setTokens([1, 2, 3]);
    }

    /**
     * testAcceptInvokesVisitFileOnGivenVisitor
     */
    public function testAcceptInvokesVisitFileOnGivenVisitor(): void
    {
        $visitor = $this->getMockBuilder('\\PDepend\\Source\\ASTVisitor\\ASTVisitor')
            ->getMock();
        $visitor->expects($this->once())
            ->method('visitCompilationUnit')
            ->with($this->isInstanceOf('PDepend\\Source\\AST\\ASTCompilationUnit'));

        $file = new ASTCompilationUnit(null);
        $file->accept($visitor);
    }

    /**
     * testMagicStringMethodReturnsEmptyStringWhenFileNameIsNull
     */
    public function testMagicStringMethodReturnsEmptyStringWhenFileNameIsNull(): void
    {
        $file = new ASTCompilationUnit(null);
        $this->assertSame('', $file->__toString());
    }

    /**
     * testMagicStringMethodReturnInjectedFileNameValue
     */
    public function testMagicStringMethodReturnInjectedFileNameValue(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(__FILE__, $file->__toString());
    }

    /**
     * testMagicSleepMethodReturnsExpectedSetOfPropertyNames
     */
    public function testMagicSleepMethodReturnsExpectedSetOfPropertyNames(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(
            [
                'cache',
                'childNodes',
                'comment',
                'endLine',
                'fileName',
                'startLine',
                'id',
            ],
            $file->__sleep()
        );
    }

    /**
     * testMagicWakeupMethodInvokesSetSourceFileOnChildNodes
     */
    public function testMagicWakeupMethodInvokesSetSourceFileOnChildNodes(): void
    {
        $node = $this->getMockBuilder('PDepend\\Source\\AST\\ASTClass')
            ->onlyMethods(['setCompilationUnit'])
            ->setConstructorArgs([__CLASS__])
            ->getMock();
        $node->expects($this->once())
            ->method('setCompilationUnit')
            ->with($this->isInstanceOf('PDepend\\Source\\AST\\ASTCompilationUnit'));

        $compilationUnit = new ASTCompilationUnit(__FILE__);
        $compilationUnit->addChild($node);
        $compilationUnit->__wakeup();
    }

    /**
     * testIsCachedReturnsFalseByDefault
     */
    public function testIsCachedReturnsFalseByDefault(): void
    {
        $file = new ASTCompilationUnit(null);
        $this->assertFalse($file->isCached());
    }

    /**
     * testIsCachedReturnsFalseWhenObjectGetsSerialized
     */
    public function testIsCachedReturnsFalseWhenObjectGetsSerialized(): void
    {
        $file = new ASTCompilationUnit(null);
        serialize($file);

        $this->assertFalse($file->isCached());
    }

    /**
     * testIsCachedReturnsTrueAfterCallToWakeup
     */
    public function testIsCachedReturnsTrueAfterCallToWakeup(): void
    {
        $file = new ASTCompilationUnit(null);
        $file = unserialize(serialize($file));

        $this->assertTrue($file->isCached());
    }

    /**
     * testGetStartLineReturnsZeroWhenSourceFileNotExists
     */
    public function testGetStartLineReturnsZeroWhenSourceFileNotExists(): void
    {
        $file = new ASTCompilationUnit(null);
        $this->assertSame(0, $file->getStartLine());
    }

    /**
     * testGetStartLineReturnsOneWhenSourceFileExists
     */
    public function testGetStartLineReturnsOneWhenSourceFileExists(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(1, $file->getStartLine());
    }

    /**
     * testGetEndLineReturnsZeroWhenSourceFileNotExists
     */
    public function testGetEndLineReturnsZeroWhenSourceFileNotExists(): void
    {
        $file = new ASTCompilationUnit(null);
        $this->assertSame(0, $file->getEndLine());
    }

    /**
     * testGetEndLineReturnsOneWhenSourceFileExists
     */
    public function testGetEndLineReturnsOneWhenSourceFileExists(): void
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertSame($this->getEndLineOfThisFile(), $file->getEndLine());
    }

    /**
     * testGetSourceReturnsNullWhenSourceFileNotExists
     */
    public function testGetSourceReturnsNullWhenSourceFileNotExists(): void
    {
        $file = new ASTCompilationUnit(null);
        $this->assertNull($file->getSource());
    }

    /**
     * Tests the {@link \PDepend\Source\AST\ASTCompilationUnit#getSource()} method.
     */
    public function testGetSourceReturnsOriginalFileContents(): void
    {
        $file = new ASTCompilationUnit($this->createCodeResourceUriForTest());

        $actual   = $file->getSource();
        $expected = file_get_contents($this->createCodeResourceUriForTest());

        $this->assertEquals($expected, $actual);
    }

    private function getEndLineOfThisFile()
    {
        return __LINE__ + 3;
    }
}
