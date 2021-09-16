<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DOMDocument;
use Exception;
use SimpleXMLElement;

class JunitOutputFormatter implements OutputFormatter
{
    /**
     * @throws Exception
     */
    public function outputResults(AnalysisResults $analysisResults): string
    {
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

        $xml = $this->getXmlString();
        $test = new SimpleXMLElement($xml);
        $dom = new DOMDocument('1.0');

        $testCount = (string) $analysisResults->getCount();
        $test->attributes()->tests = $testCount;
        $test->attributes()->failures = $testCount;

        $suitCount = 0;
        $caseCount = 0;
        $oldRel = null;
        $testsuite = null;
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $details = $analysisResult->getFullDetails();
            $type = $details['type'] ?? 'error';
            $type = strtolower($type);
            $column = $details['column'] ?? '0';

            $relativeFileName = $analysisResult->getLocation()->getRelativeFileName()->getFileName();

            if ($oldRel !== $relativeFileName || null === $testsuite) {
                $testsuite = $test->addChild('testsuite');
                $testsuite->addAttribute('errors', '0');
                $testsuite->addAttribute('tests', (string) $caseCount);
                $testsuite->addAttribute('failures', (string) $caseCount);
                $testsuite->addAttribute('name', $relativeFileName);

                $oldRel = $relativeFileName;
                ++$suitCount;
                $caseCount = 0;
            }

            $lineSprint = sprintf(
                '%s at %s (%s:%s)',
                $analysisResult->getType()->getType(),
                $analysisResult->getLocation()->getAbsoluteFileName()->getFileName(),
                (string) $analysisResult->getLocation()->getLineNumber()->getLineNumber(),
                $column
            );
            $testcase = $testsuite->addChild('testcase');
            $testcase->addAttribute('name', $lineSprint);
            $testcase->addChild('failure');
            $testcase->failure->addAttribute('type', $type);
            $testcase->failure->addAttribute(
                'message',
                $analysisResult->getMessage()
            );
            ++$caseCount;
            $testsuite->attributes()->tests = $caseCount;
            $testsuite->attributes()->failures = $caseCount;
        }
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($test->asXML());

        return $dom->saveXML();
    }

    private function getXmlString(): string
    {
        $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testsuites
        name="SARB" tests="1" failures="0">
</testsuites>

XML;

        return $xmlstr;
    }

    public function getIdentifier(): string
    {
        return 'junit';
    }
}
