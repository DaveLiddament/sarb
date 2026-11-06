<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
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
        $this->expectException(SarbException::class);
        JsonUtils::toString([
            \INF,
        ]);
    }

    public function testToStringSubstitutesInvalidUtf8(): void
    {
        // \xE9 is é in ISO-8859-1; it is not valid UTF-8
        $output = JsonUtils::toString([
            'message' => "caf\xE9",
        ]);

        $this->assertSame("{\n    \"message\": \"caf\u{FFFD}\"\n}", $output);
    }
}
