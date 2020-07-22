<?php
declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpMdTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpMdTextResultsParser\PhpMdTextIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpMdTextResultsParser\PhpMdTextIdentifier
 */
class PhpMdTextIdentifierTest extends TestCase
{
    /** @var PhpMdTextIdentifier */
    private $identifier;

    public function setUp()
    {
        $this->identifier = new PhpMdTextIdentifier();
    }

    /**
     * @covers ::getCode
     */
    public function testGetCode(): void
    {
        static::assertSame('phpmd-text', $this->identifier->getCode());
    }

    /**
     * @covers ::getDescription
     */
    public function testGetDescription(): void
    {
        static::assertSame(
            'PHP Mess Detector. To generate use: phpmd <code_directory> text <phpmd.xml location> --reportfile <output.txt>',
            $this->identifier->getDescription()
        );
    }
}
