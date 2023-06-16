<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class JunitOutputFormatter implements OutputFormatter
{
    /**
     * @throws SarbException
     */
    public function outputResults(AnalysisResults $analysisResults): string
    {
        if (!extension_loaded('simplexml')) {
            throw new SarbException('Simple XML required for JUnit output format'); // @codeCoverageIgnore
        }

        if (0 === $analysisResults->getCount()) {
            return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testsuites
        name="SARB" tests="1" failures="0">
    <testsuite errors="0" tests="1" failures="0" name="Success">
        <testcase name="Success"/>
    </testsuite>
</testsuites>

XML;
        }

        $xml = $this->getXmlString($analysisResults->getCount());
        $test = new \SimpleXMLElement($xml);

        $caseCount = 0;
        $previousRelativeFileName = null;
        $testsuite = null;
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $details = $analysisResult->getFullDetails();

            /** @psalm-suppress MixedAssignment */
            $column = $details['column'] ?? null;
            if (is_numeric($column)) {
                $column = (string) ((int) $column);
            } else {
                $column = '0';
            }

            $relativeFileName = $analysisResult->getLocation()->getRelativeFileName()->getFileName();

            if ((null === $testsuite) || ($previousRelativeFileName !== $relativeFileName)) {
                // Add final counts to previous testsuite (if one exists)
                $this->addCounts($testsuite, $caseCount);

                $testsuite = $test->addChild('testsuite');
                $testsuite->addAttribute('name', $relativeFileName);

                $previousRelativeFileName = $relativeFileName;
                $caseCount = 0;
            }

            $lineSprint = sprintf(
                '%s at %s (%d:%s)',
                $analysisResult->getType()->getType(),
                $analysisResult->getLocation()->getAbsoluteFileName()->getFileName(),
                $analysisResult->getLocation()->getLineNumber()->getLineNumber(),
                $column
            );
            $testcase = $testsuite->addChild('testcase');
            $testcase->addAttribute('name', $lineSprint);
            $failure = $testcase->addChild('failure');
            $failure->addAttribute('type', $analysisResult->getSeverity()->getSeverity());
            $failure->addAttribute(
                'message',
                $analysisResult->getMessage()
            );
            ++$caseCount;
        }

        $this->addCounts($testsuite, $caseCount);

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $asXml = $test->asXML();

        if ((false !== $asXml) && ('' !== $asXml)) {
            $dom->loadXML($asXml);
        } else {
            throw new SarbException('xml could not be loaded'); // @codeCoverageIgnore
        }
        $saveXml = $dom->saveXML();
        if (false !== $saveXml) {
            return $saveXml;
        }
        throw new SarbException('dom could not be saved'); // @codeCoverageIgnore
    }

    private function getXmlString(int $issues): string
    {
        $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testsuites
        name="SARB" tests="{$issues}" failures="{$issues}">
</testsuites>

XML;

        return $xmlstr;
    }

    public function getIdentifier(): string
    {
        return 'junit';
    }

    private function addCounts(?\SimpleXMLElement $testsuite, int $caseCount): void
    {
        if (null !== $testsuite) {
            $testsuite->addAttribute('errors', '0');
            $testsuite->addAttribute('tests', (string) $caseCount);
            $testsuite->addAttribute('failures', (string) $caseCount);
        }
    }
}
