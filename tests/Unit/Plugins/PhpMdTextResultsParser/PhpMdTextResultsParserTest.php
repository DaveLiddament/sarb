<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpMdTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpMdTextResultsParser\PhpMdTextResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpMdTextResultsParser\PhpMdTextResultsParser
 */
class PhpMdTextResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var PhpMdTextResultsParser
     */
    private $phpmdTextResultsParser;

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp()
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->phpmdTextResultsParser = new PhpMdTextResultsParser(new FqcnRemover());
        $this->fileContents = $this->getResource('phpmd/phpmd.txt');
    }

    /**
     * @covers ::convertFromString
     *
     * @throws InvalidFileFormatException
     * @throws ParseAtLocationException
     */
    public function testConversionFromString(): void
    {
        $analysisResults = $this->phpmdTextResultsParser->convertFromString($this->fileContents, $this->projectRoot);

        static::assertCount(7, $analysisResults->getAnalysisResults());

        $expectedResult = [
            ['Lib/Referrer/ReferrerOwnMapper.php', 18, 'Avoid excessively long variable names like $allowedColumnNamesUser. Keep variable name length under 20.'],
            ['Lib/Returns/Product/ReturnProductRepository.php', 26, 'Avoid excessively long variable names like $productCombinationRepo. Keep variable name length under 20.'],
            ['Lib/Returns/ReturnsRepository.php', 37, 'Avoid excessively long variable names like $productCombinationRepo. Keep variable name length under 20.'],
            ['Lib/Search/Elastic/Content/ContentSearchPersistence.php', 60, 'Missing class import via use statement (line \'60\', column \'44\').'],
            ['Lib/Search/Elastic/ElasticSearchBase.php', 18, 'The class ElasticSearchBase has an overall complexity of 52 which is very high. The configured complexity threshold is 50.'],
            ['Lib/Search/Elastic/Event/SearchEventManager.php', 26, 'Avoid excessively long variable names like $searchEventSubscribers. Keep variable name length under 20.'],
            ['Lib/Search/Elastic/Monitor/SearchMonitor.php', 44, 'Avoid excessively long variable names like $suggestionResultCount. Keep variable name length under 20.'],
        ];

        foreach ($analysisResults->getAnalysisResults() as $key => $analysisResult) {
            [$filePath, $lineNumber, $message] = $expectedResult[$key];

            $location = new Location(new FileName($filePath), new LineNumber($lineNumber));
            $type = new Type($message);

            static::assertTrue($analysisResult->isMatch($location, $type));
            static::assertSame($message, $analysisResult->getMessage());
        }
    }

    /**
     * @covers ::convertToString
     *
     * @throws InvalidFileFormatException
     * @throws JsonParseException
     * @throws ParseAtLocationException
     */
    public function testConvertToString(): void
    {
        $analysisResults = $this->phpmdTextResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $asString = $this->phpmdTextResultsParser->convertToString($analysisResults);

        $this->assertFileContentsSame($this->fileContents, $asString);
    }

    public function testTypeGuesser(): void
    {
        static::assertFalse($this->phpmdTextResultsParser->showTypeGuessingWarning());
    }
}
