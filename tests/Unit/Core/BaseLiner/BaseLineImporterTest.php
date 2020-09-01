<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\HistoryFactoryRegistry;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\ResultsParsersRegistry;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitDiffHistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class BaseLineImporterTest extends TestCase
{
    use ResourceLoaderTrait;

    /**
     * @var BaseLineImporter
     */
    private $baseLineImporter;
    /**
     * @var GitDiffHistoryFactory
     */
    private $gitHistoryFactory;
    /**
     * @var SarbJsonResultsParser
     */
    private $sarbJsonResultsParser;

    protected function setUp(): void
    {
        $fileReader = new FileReader();

        $this->sarbJsonResultsParser = new SarbJsonResultsParser();
        $resultsLookupService = new ResultsParsersRegistry([
            $this->sarbJsonResultsParser,
        ]);

        $this->gitHistoryFactory = new GitDiffHistoryFactory(new GitCliWrapper(), new Parser());
        $historyLookupService = new HistoryFactoryRegistry([
            $this->gitHistoryFactory,
        ]);

        $this->baseLineImporter = new BaseLineImporter($fileReader, $resultsLookupService, $historyLookupService);
    }

    /**
     * @phpstan-return array<mixed>
     */
    public function invalidFileDataProvider(): array
    {
        return [
            ['invalid-json.json'],
            ['baseline/invalid-no-history-marker.json'],
            ['baseline/invalid-no-results-parser.json'],
            ['baseline/invalid-no-history-analyser.json'],
            ['baseline/invalid-no-analysis-results.json'],
            ['baseline/invalid-results.json'],
            ['baseline/invalid-results-parser.json'],
            ['baseline/invalid-history-analyser.json'],
            ['baseline/invalid-history-marker.json'],
        ];
    }

    /**
     * @dataProvider invalidFileDataProvider
     */
    public function testInvalidFileFormat(string $relativeFileName): void
    {
        $this->expectException(BaseLineImportException::class);
        $this->getBaseLine($relativeFileName);
    }

    public function testValidEmptyBaseLine(): void
    {
        $baseLine = $this->getBaseLine('baseline/empty-baseline.json');
        $this->assertCount(0, $baseLine->getAnalysisResults()->asArray());

        $historyMarker = $baseLine->getHistoryMarker();
        $this->assertInstanceOf(GitCommit::class, $historyMarker);
        $this->assertSame('9be6101dc6848d67b5958a5762494060062e42ec', $historyMarker->asString());

        $this->assertSame($this->gitHistoryFactory, $baseLine->getHistoryFactory());
        $this->assertSame($this->sarbJsonResultsParser, $baseLine->getResultsParser());
    }

    public function testBaseLineWithOneEntry(): void
    {
        $baseLine = $this->getBaseLine('baseline/baseline.json');
        $this->assertCount(1, $baseLine->getAnalysisResults()->asArray());
    }

    private function getBaseLine(string $relativeFileName): BaseLine
    {
        $fullFilePath = $this->getPath($relativeFileName);
        $fileName = new FileName($fullFilePath);

        return $this->baseLineImporter->import($fileName);
    }
}
