<?php
/**
 * This file is part of PDepend.
 * 
 * PHP Version 5
 *
 * Copyright (c) 2008-2013, Manuel Pichler <mapi@pdepend.org>.
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
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
  */

namespace PDepend\Source\AST;

use PDepend\AbstractTest;

/**
 * Test case for the code file class.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @covers \PDepend\Source\AST\ASTCompilationUnit
 * @group unittest
 */
class ASTCompilationUnitTest extends AbstractTest
{
    /**
     * testGetNameReturnsTheFileName
     * 
     * @return void
     */
    public function testGetNameReturnsTheFileName()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(__FILE__, $file->getName());
    }

    /**
     * testGetFileNameReturnsTheFileName
     *
     * @return void
     */
    public function testGetFileNameReturnsTheFileName()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(__FILE__, $file->getFileName());
    }

    /**
     * testGetUuidReturnsNullByDefault
     *
     * @return void
     */
    public function testGetUuidReturnsNullByDefault()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertNull($file->getUuid());
    }

    /**
     * testGetUuidReturnsInjectedUuidValue
     *
     * @return void
     */
    public function testGetUuidReturnsInjectedUuidValue()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $file->setUuid(__FUNCTION__);

        $this->assertEquals(__FUNCTION__, $file->getUuid());
    }

    /**
     * testGetDocCommentReturnsNullByDefault
     *
     * @return void
     */
    public function testGetDocCommentReturnsNullByDefault()
    {
        $file = new ASTCompilationUnit(null);
        $this->assertNull($file->getDocComment());
    }

    /**
     * testGetDocCommentReturnsInjectedDocCommentValue
     *
     * @return void
     */
    public function testGetDocCommentReturnsInjectedDocCommentValue()
    {
        $file = new ASTCompilationUnit(null);
        $file->setDocComment('/** Manuel */');

        $this->assertEquals('/** Manuel */', $file->getDocComment());
    }

    /**
     * testGetTokensDelegatesCallToCacheRestoreWithFileUuid
     *
     * @return void
     */
    public function testGetTokensDelegatesCallToCacheRestoreWithFileUuid()
    {
        $cache = $this->createCacheFixture();
        $cache->expects($this->once())
            ->method('type')
            ->with(self::equalTo('tokens'))
            ->will($this->returnValue($cache));
        $cache->expects($this->once())
            ->method('restore')
            ->with(self::equalTo(__FUNCTION__));

        $file = new ASTCompilationUnit(null);
        $file->setCache($cache);
        $file->setUuid(__FUNCTION__);

        $file->getTokens();
    }

    /**
     * testSetTokensDelegatesCallToCacheStoreWithFileUuid
     *
     * @return void
     */
    public function testSetTokensDelegatesCallToCacheStoreWithFileUuid()
    {
        $cache = $this->createCacheFixture();
        $cache->expects($this->once())
            ->method('type')
            ->with(self::equalTo('tokens'))
            ->will($this->returnValue($cache));
        $cache->expects($this->once())
            ->method('store')
            ->with(self::equalTo(__FUNCTION__), self::equalTo(array(1, 2, 3)));

        $file = new ASTCompilationUnit(null);
        $file->setCache($cache);
        $file->setUuid(__FUNCTION__);

        $file->setTokens(array(1, 2, 3));
    }

    /**
     * testAcceptInvokesVisitFileOnGivenVisitor
     *
     * @return void
     */
    public function testAcceptInvokesVisitFileOnGivenVisitor()
    {
        $visitor = $this->getMock('\\PDepend\\Source\\ASTVisitor\\ASTVisitor');
        $visitor->expects($this->once())
            ->method('visitFile')
            ->with(self::isInstanceOf(ASTCompilationUnit::CLAZZ));

        $file = new ASTCompilationUnit(null);
        $file->accept($visitor);
    }

    /**
     * testMagicStringMethodReturnsEmptyStringWhenFileNameIsNull
     *
     * @return void
     */
    public function testMagicStringMethodReturnsEmptyStringWhenFileNameIsNull()
    {
        $file = new ASTCompilationUnit(null);
        $this->assertSame('', $file->__toString());
    }

    /**
     * testMagicStringMethodReturnInjectedFileNameValue
     *
     * @return void
     */
    public function testMagicStringMethodReturnInjectedFileNameValue()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(__FILE__, $file->__toString());
    }

    /**
     * testMagicSleepMethodReturnsExpectedSetOfPropertyNames
     * 
     * @return void
     */
    public function testMagicSleepMethodReturnsExpectedSetOfPropertyNames()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(
            array(
                'cache',
                'childNodes',
                'docComment',
                'endLine',
                'fileName',
                'startLine',
                'uuid'
            ),
            $file->__sleep()
        );
    }

    /**
     * testMagicWakeupMethodInvokesSetSourceFileOnChildNodes
     *
     * @return void
     */
    public function testMagicWakeupMethodInvokesSetSourceFileOnChildNodes()
    {
        $node = $this->getMock(
            ASTClass::CLAZZ,
            array('setSourceFile'),
            array(__CLASS__)
        );
        $node->expects($this->once())
            ->method('setSourceFile')
            ->with(self::isInstanceOf(ASTCompilationUnit::CLAZZ));

        $file = new ASTCompilationUnit(__FILE__);
        $file->addChild($node);
        $file->__wakeup();
    }

    /**
     * testIsCachedReturnsFalseByDefault
     *
     * @return void
     */
    public function testIsCachedReturnsFalseByDefault()
    {
        $file = new ASTCompilationUnit(null);
        $this->assertFalse($file->isCached());
    }

    /**
     * testIsCachedReturnsFalseWhenObjectGetsSerialized
     *
     * @return void
     */
    public function testIsCachedReturnsFalseWhenObjectGetsSerialized()
    {
        $file = new ASTCompilationUnit(null);
        serialize($file);

        $this->assertFalse($file->isCached());
    }

    /**
     * testIsCachedReturnsTrueAfterCallToWakeup
     *
     * @return void
     */
    public function testIsCachedReturnsTrueAfterCallToWakeup()
    {
        $file = new ASTCompilationUnit(null);
        $file = unserialize(serialize($file));

        $this->assertTrue($file->isCached());
    }

    /**
     * testGetStartLineReturnsZeroWhenSourceFileNotExists
     *
     * @return void
     */
    public function testGetStartLineReturnsZeroWhenSourceFileNotExists()
    {
        $file = new ASTCompilationUnit(null);
        $this->assertSame(0, $file->getStartLine());
    }

    /**
     * testGetStartLineReturnsOneWhenSourceFileExists
     *
     * @return void
     */
    public function testGetStartLineReturnsOneWhenSourceFileExists()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(1, $file->getStartLine());
    }

    /**
     * testGetEndLineReturnsZeroWhenSourceFileNotExists
     *
     * @return void
     */
    public function testGetEndLineReturnsZeroWhenSourceFileNotExists()
    {
        $file = new ASTCompilationUnit(null);
        $this->assertSame(0, $file->getEndLine());
    }

    /**
     * testGetEndLineReturnsOneWhenSourceFileExists
     *
     * @return void
     */
    public function testGetEndLineReturnsOneWhenSourceFileExists()
    {
        $file = new ASTCompilationUnit(__FILE__);
        $this->assertEquals(362, $file->getEndLine());
    }

    /**
     * testGetSourceReturnsNullWhenSourceFileNotExists
     *
     * @return void
     */
    public function testGetSourceReturnsNullWhenSourceFileNotExists()
    {
        $file = new ASTCompilationUnit(null);
        $this->assertNull($file->getSource());
    }

    /**
     * Tests the {@link \PDepend\Source\AST\ASTCompilationUnit#getSource()} method.
     *
     * @return void
     */
    public function testGetSourceReturnsOriginalFileContents()
    {
        $file = new ASTCompilationUnit(self::createCodeResourceUriForTest());

        $actual   = $file->getSource();
        $expected = file_get_contents(self::createCodeResourceUriForTest());

        $this->assertEquals($expected, $actual);
    }
}
