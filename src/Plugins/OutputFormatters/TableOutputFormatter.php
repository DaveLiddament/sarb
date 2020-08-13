<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\SummaryStats;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Assert\Assert;

class TableOutputFormatter implements OutputFormatter
{
    public function outputResults(SummaryStats $summaryStats, AnalysisResults $analysisResults): string
    {
        $output = <<<EOF
Latest issue count: {$summaryStats->getLatestAnalysisResultsCount()}
Baseline issue count: {$summaryStats->getBaseLineCount()}
Issues count with baseline removed: {$analysisResults->getCount()}
EOF;

        if ($analysisResults->hasNoIssues()) {
            return $output;
        }

        $bufferedOutput = new BufferedOutput();
        $this->addIssuesInTable($bufferedOutput, $analysisResults);

        $output .= PHP_EOL;
        $output .= $bufferedOutput->fetch();

        return $output;
    }

    public function getIdentifier(): string
    {
        return 'table';
    }

    private function addIssuesInTable(BufferedOutput $output, AnalysisResults $analysisResults): void
    {
        /** @var string[] $headings */
        $headings = [
            'Line',
            'Description',
        ];

        /** @var FileName $currentFileName */
        $currentFileName = null;
        /** @var Table|null $currentTable */
        $currentTable = null;
        foreach ($analysisResults->getOrderedAnalysisResults() as $analysisResult) {
            $fileName = $analysisResult->getLocation()->getFileName();

            if (!$fileName->isEqual($currentFileName)) {
                $this->renderTable($currentTable);

                $output->writeln("\nFILE: {$fileName->getFileName()}");
                $currentFileName = $fileName;
                $currentTable = new Table($output);
                $currentTable->setHeaders($headings);
            }

            Assert::notNull($currentTable);
            $currentTable->addRow([
                $analysisResult->getLocation()->getLineNumber()->getLineNumber(),
                $analysisResult->getMessage(),
            ]);
        }

        $this->renderTable($currentTable);
    }

    private function renderTable(?Table $table): void
    {
        if (null !== $table) {
            $table->render();
        }
    }
}
