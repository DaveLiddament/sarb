<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Assert\Assert;

class TableOutputFormatter implements OutputFormatter
{
    public const CODE = 'table';

    public function outputResults(AnalysisResults $analysisResults): string
    {
        if ($analysisResults->hasNoIssues()) {
            return 'No issues';
        }

        $bufferedOutput = new BufferedOutput();
        $this->addIssuesInTable($bufferedOutput, $analysisResults);

        return $bufferedOutput->fetch();
    }

    public function getIdentifier(): string
    {
        return self::CODE;
    }

    private function addIssuesInTable(BufferedOutput $output, AnalysisResults $analysisResults): void
    {
        /** @var string[] $headings */
        $headings = [
            'Line',
            'Description',
        ];

        /** @var AbsoluteFileName $currentFileName */
        $currentFileName = null;
        /** @var Table|null $currentTable */
        $currentTable = null;
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $fileName = $analysisResult->getLocation()->getAbsoluteFileName();

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
