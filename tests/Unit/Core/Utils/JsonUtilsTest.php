<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use PHPUnit\Framework\TestCase;

final class JsonUtilsTest extends TestCase
{
    public function testToArrayHappyPath(): void
    {
        $string = '{"name" : "dave"}';
        $asArray = JsonUtils::toArray($string);
        $this->assertSame([
            'name' => 'dave',
        ],
            $asArray);
    }

    public function testToArrayInvalidData(): void
    {
        $string = '{';
        $this->expectException(InvalidContentTypeException::class);
        JsonUtils::toArray($string);
    }

    public function testToStringHappyPath(): void
    {
        $expected = <<<EOF
{
    "name": "dave"
}
EOF;

        $output = JsonUtils::toString([
            'name' => 'dave',
        ]);
        $this->assertSame($expected, $output);
    }

    public function testToStringInvalidData(): void
    {
        $this->expectException(\LogicException::class);
        JsonUtils::toString([
            \INF,
        ]);
    }
}
