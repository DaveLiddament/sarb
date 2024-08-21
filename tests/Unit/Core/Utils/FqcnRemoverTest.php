<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use PHPUnit\Framework\TestCase;

final class FqcnRemoverTest extends TestCase
{
    /**
     * @psalm-return array<string,array{string,string}>
     */
    public function dataProvider(): array
    {
        return [
            'FQCN with method name' => [
                'Method DaveLiddament\StaticAnalysisBaseliner\Core\Common\Location::__construct() has parameter $fileName with no typehint specified.',
                'Method has parameter $fileName with no typehint specified.',
            ],
            'FQCN no method name' => [
                'Demo\Employee has an uninitialized variable $this->age, but no constructor',
                'has an uninitialized variable $this->age, but no constructor',
            ],
            'No FQCN' => [
                'PHPDoc tag @param has invalid value ($fileName): Unexpected token expected TOKEN_IDENTIFIER at offset 54',
                'PHPDoc tag @param has invalid value ($fileName): Unexpected token expected TOKEN_IDENTIFIER at offset 54',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFqcnRemover(string $input, string $expected): void
    {
        $fqcnRemove = new FqcnRemover();
        $actual = $fqcnRemove->removeRqcn($input);
        $this->assertEquals($expected, $actual);
    }
}
