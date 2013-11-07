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
 * @deprecated Since release 0.10.5, please use the summary logger
 */

namespace PDepend\Report\Phpunit;

use PDepend\Metrics\Analyzer;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Metrics\AnalyzerProjectAware;
use PDepend\Report\CodeAwareGenerator;
use PDepend\Report\FileAwareGenerator;
use PDepend\Report\NoLogOutputException;
use PDepend\Source\AST\AbstractASTArtifact;
use PDepend\Source\AST\AbstractASTClassOrInterface;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTCompilationUnit;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;

/**
 * This logger provides a xml log file, that is compatible with the files
 * generated by the <a href="http://www.Phpunit.de">PHPUnit</a> --log-metrics
 * option.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @deprecated Since release 0.10.5, please use the summary logger
 */
class Xml extends AbstractASTVisitor implements CodeAwareGenerator, FileAwareGenerator
{
    /**
     * The log output file.
     *
     * @var string
     */
    private $logFile = null;

    /**
     * The raw {@link \PDepend\Source\AST\ASTNamespace} instances.
     *
     * @var \PDepend\Source\AST\ASTArtifactList
     */
    protected $code = null;

    /**
     * List of all generated project metrics.
     *
     * @var array(string=>mixed)
     */
    protected $projectMetrics = array();

    /**
     * List of all analyzers that implement the node aware interface
     * {@link \PDepend\Metrics\AnalyzerNodeAware}.
     *
     * @var \PDepend\Metrics\AnalyzerNodeAware[]
     */
    private $nodeAwareAnalyzers = array();

    /**
     * The internal used xml stack.
     *
     * @var \DOMElement[]
     */
    private $xmlStack = array();

    /**
     * Number of visited files.
     *
     * @var integer
     */
    private $files = 0;

    /**
     * This property contains some additional metrics for the file-DOMElement.
     *
     * @var array(string=>integer)
     */
    private $additionalFileMetrics = array(
        'classes'    =>  0,
        'functions'  =>  0
    );

    /**
     * This translation table maps some PDepend identifiers with the
     * corresponding PHPUnit identifiers.
     *
     * @var array(string=>string)
     */
    private $phpunitTranslationTable = array(
        'ccn2'    =>  'ccn',
        'noc'     =>  'classes',
        'noi'     =>  'interfs',
        'nof'     =>  'functions',
        'eloc'    =>  'locExecutable',
        'maxDIT'  =>  'maxdit',
    );

    /**
     * Returns an <b>array</b> with accepted analyzer types. These types can be
     * concrete analyzer classes or one of the descriptive analyzer interfaces.
     *
     * @return array(string)
     */
    public function getAcceptedAnalyzers()
    {
        return array(
            'PDepend\\Metrics\\AnalyzerNodeAware',
            'PDepend\\Metrics\\AnalyzerProjectAware'
        );
    }

    /**
     * Sets the output log file.
     *
     * @param string $logFile The output log file.
     *
     * @return void
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * Sets the context code nodes.
     *
     * @param \PDepend\Source\AST\ASTArtifactList $artifacts
     * @return void
     */
    public function setArtifacts(ASTArtifactList $artifacts)
    {
        $this->code = $artifacts;
    }

    /**
     * Adds an analyzer to log. If this logger accepts the given analyzer it
     * with return <b>true</b>, otherwise the return value is <b>false</b>.
     *
     * @param \PDepend\Metrics\Analyzer $analyzer The analyzer to log.
     * @return boolean
     */
    public function log(Analyzer $analyzer)
    {
        $accept = false;

        if ($analyzer instanceof AnalyzerProjectAware) {
            // Get project metrics
            $metrics = $analyzer->getProjectMetrics();
            // Merge with existing metrics.
            $this->projectMetrics = array_merge($this->projectMetrics, $metrics);

            $accept = true;
        }
        if ($analyzer instanceof AnalyzerNodeAware) {
            $this->nodeAwareAnalyzers[] = $analyzer;

            $accept = true;
        }

        return $accept;
    }

    /**
     * Closes the logger process and writes the output file.
     *
     * @return void
     * @throws \PDepend\Report\NoLogOutputException If the no log target exists.
     */
    public function close()
    {
        trigger_error(
            'The --phpunit-xml log option is deprecated.',
            E_USER_DEPRECATED
        );

        if ($this->logFile === null) {
            throw new NoLogOutputException($this);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $dom->formatOutput = true;

        // Using XPath is only possible, when we add it to the document!?!?
        // Is this this correct?
        $metricsXml = $dom->appendChild($dom->createElement('metrics'));

        array_push($this->xmlStack, $metricsXml);

        foreach ($this->code as $node) {
            $node->accept($this);
        }

        // Create project metrics and apply Phpunit translation table
        $metrics = array_merge(
            $this->projectMetrics,
            array('files'  =>  $this->files)
        );
        $metrics = $this->applyPhpUnitTranslationTable($metrics);

        // Sort metrics
        ksort($metrics);

        // Append project metrics
        foreach ($metrics as $name => $value) {
            $metricsXml->setAttribute($name, $value);
        }

        $dom->save($this->logFile);
    }

    /**
     * Visits a class node.
     *
     * @param \PDepend\Source\AST\ASTClass $class
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        $this->visitType($class);
    }

    /**
     * Visits a file node.
     *
     * @param \PDepend\Source\AST\ASTCompilationUnit $compilationUnit
     * @return void
     */
    public function visitFile(ASTCompilationUnit $compilationUnit)
    {
        $metricsXml = end($this->xmlStack);
        $document   = $metricsXml->ownerDocument;

        $xpath  = new DOMXPath($document);
        $result = $xpath->query("/metrics/file[@name='{$compilationUnit->getFileName()}']");

        // Only add a new file
        if ($result->length === 0) {
            // Create a new file element
            $fileXml = $document->createElement('file');
            // Set source file name
            $fileXml->setAttribute('name', $compilationUnit->getFileName());

            // Append all metrics
            $this->appendMetrics($fileXml, $compilationUnit, $this->additionalFileMetrics);

            // Append file to metrics xml
            $metricsXml->appendChild($fileXml);

            // Update project file counter
            ++$this->files;
        } else {
            $fileXml = $result->item(0);
        }

        // Add file to stack
        array_push($this->xmlStack, $fileXml);
    }

    /**
     * Visits a function node.
     *
     * @param \PDepend\Source\AST\ASTFunction $function
     * @return void
     */
    public function visitFunction(ASTFunction $function)
    {
        // First visit function file
        $function->getSourceFile()->accept($this);

        $fileXml  = end($this->xmlStack);
        $document = $fileXml->ownerDocument;

        $functionXml = $document->createElement('function');
        $functionXml->setAttribute('name', $function->getName());

        $this->appendMetrics($functionXml, $function);

        $fileXml->appendChild($functionXml);

        // Update file element @functions count
        $fileXml->setAttribute('functions', 1 + $fileXml->getAttribute('functions'));

        // Remove xml file element
        array_pop($this->xmlStack);
    }

    /**
     * Visits a code interface object.
     *
     * @param \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function visitInterface(ASTInterface $interface)
    {
        $this->visitType($interface);
    }

    /**
     * Visits a method node.
     *
     * @param \PDepend\Source\AST\ASTMethod $method
     * @return void
     */
    public function visitMethod(ASTMethod $method)
    {
        $classXml = end($this->xmlStack);
        $document = $classXml->ownerDocument;

        $methodXml = $document->createElement('method');
        $methodXml->setAttribute('name', $method->getName());

        $this->appendMetrics($methodXml, $method);

        $classXml->appendChild($methodXml);
    }

    /**
     * Generic visit method for classes and interfaces.
     *
     * @param \PDepend\Source\AST\AbstractASTClassOrInterface $type
     * @return void
     */
    private function visitType(AbstractASTClassOrInterface $type)
    {
        $type->getSourceFile()->accept($this);

        $fileXml  = end($this->xmlStack);
        $document = $fileXml->ownerDocument;

        $classXml = $document->createElement('class');
        $classXml->setAttribute('name', $type->getName());

        $this->appendMetrics($classXml, $type);

        $fileXml->appendChild($classXml);

        array_push($this->xmlStack, $classXml);

        foreach ($type->getMethods() as $method) {
            $method->accept($this);
        }

        $fileXml->setAttribute('classes', 1 + $fileXml->getAttribute('classes'));

        // Remove xml class element
        array_pop($this->xmlStack);
        // Remove xml file element
        array_pop($this->xmlStack);
    }

    /**
     * Aggregates all metrics for the given <b>$node</b> instance and adds them
     * to the <b>DOMElement</b>
     *
     * @param \DOMElement $xml
     * @param \PDepend\Source\AST\AbstractASTArtifact $node
     * @param array(string=>mixed) $metrics
     * @return void
     */
    private function appendMetrics(
        \DOMElement $xml,
        AbstractASTArtifact $node,
        array $metrics = array()
    ) {
        foreach ($this->nodeAwareAnalyzers as $analyzer) {
            $metrics = array_merge($metrics, $analyzer->getNodeMetrics($node));
        }
        $metrics = $this->applyPhpUnitTranslationTable($metrics);

        ksort($metrics);
        foreach ($metrics as $name => $value) {
            $xml->setAttribute($name, $value);
        }
    }

    /**
     * Translates PDepend metric names into PHPUnit names.
     *
     * @param array(string=>mixed) $metrics Set of collected metric values.
     *
     * @return array(string=>mixed)
     */
    private function applyPhpUnitTranslationTable(array $metrics)
    {
        foreach ($this->phpunitTranslationTable as $pdepend => $phpunit) {
            if (!isset($metrics[$pdepend])) {
                continue;
            }
            $metrics[$phpunit] = $metrics[$pdepend];

            unset($metrics[$pdepend]);
        }
        return $metrics;
    }
}
