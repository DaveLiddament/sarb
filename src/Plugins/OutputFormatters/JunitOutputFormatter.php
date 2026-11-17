<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use Webmozart\Assert\Assert;

final class JunitOutputFormatter implements OutputFormatter
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
                Assert::notNull($testsuite, 'Can not add testsuite to XML');
                $testsuite->addAttribute('name', $this->sanitiseForXml($relativeFileName));

                $previousRelativeFileName = $relativeFileName;
                $caseCount = 0;
            }

            $lineSprint = sprintf(
                '%s at %s (%d:%s)',
                $analysisResult->getType()->getType(),
                $analysisResult->getLocation()->getAbsoluteFileName()->getFileName(),
                $analysisResult->getLocation()->getLineNumber()->getLineNumber(),
                $column,
            );
            $testcase = $testsuite->addChild('testcase');
            Assert::notNull($testcase, 'Can not add testcase element to XML');
            $testcase->addAttribute('name', $this->sanitiseForXml($lineSprint));
            $failure = $testcase->addChild('failure');
            Assert::notNull($failure, 'Can not add failure element to XML');
            $failure->addAttribute('type', $analysisResult->getSeverity()->getSeverity());
            $failure->addAttribute(
                'message',
                $this->sanitiseForXml($analysisResult->getMessage()),
            );
            ++$caseCount;
        }

        $this->addCounts($testsuite, $caseCount);

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $asXml = $test->asXML();

        if (is_string($asXml) && ('' !== $asXml)) {
            if (!$dom->loadXML($asXml)) {
                throw new SarbException('xml could not be parsed'); // @codeCoverageIgnore
            }
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

    /**
     * Removes characters that are not valid in an XML 1.0 document (e.g. most control
     * characters). SimpleXML would write them to the attribute unescaped, producing a document
     * that can not be parsed.
     */
    private function sanitiseForXml(string $value): string
    {
        $sanitised = preg_replace(
            '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u',
            '',
            $value,
        );

        if (null === $sanitised) {
            // Value is not valid UTF-8. Keep printable ASCII characters only.
            $sanitised = (string) preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $value);
        }

        return $sanitised;
    }
}
