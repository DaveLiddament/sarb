<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ToonUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ToonUtilsTest extends TestCase
{
    #[DataProvider('scalarProvider')]
    public function testEncodeScalar(int|bool|string|null $value, string $expected): void
    {
        $this->assertSame($expected, ToonUtils::encodeScalar($value));
    }

    /**
     * @return iterable<string, array{int|bool|string|null, string}>
     */
    public static function scalarProvider(): iterable
    {
        // Non-string primitives — emitted bare
        yield 'null' => [null, 'null'];
        yield 'true' => [true, 'true'];
        yield 'false' => [false, 'false'];
        yield 'int zero' => [0, '0'];
        yield 'int positive' => [42, '42'];
        yield 'int negative' => [-5, '-5'];

        // Plain strings — unquoted
        yield 'plain word' => ['hello', 'hello'];
        yield 'internal space' => ['hello world', 'hello world'];
        yield 'internal hyphen' => ['date-2025', 'date-2025'];

        // Empty string — quoted
        yield 'empty string' => ['', '""'];

        // Forbidden characters — quoted
        yield 'comma' => ['a,b', '"a,b"'];
        yield 'colon' => ['key:value', '"key:value"'];
        yield 'double quote' => ['has "quote"', '"has \\"quote\\""'];
        yield 'backslash' => ['a\\b', '"a\\\\b"'];
        yield 'open bracket' => ['[x', '"[x"'];
        yield 'close bracket' => ['x]', '"x]"'];
        yield 'open brace' => ['{x', '"{x"'];
        yield 'close brace' => ['x}', '"x}"'];
        yield 'newline' => ["a\nb", '"a\\nb"'];
        yield 'carriage return' => ["a\rb", '"a\\rb"'];
        yield 'tab' => ["a\tb", '"a\\tb"'];

        // Other control characters (U+0000–U+001F) — quoted and escaped as \uXXXX
        yield 'null byte' => ["a\x00b", '"a\\u0000b"'];
        yield 'bell' => ["a\x07b", '"a\\u0007b"'];
        yield 'backspace' => ["a\x08b", '"a\\u0008b"'];
        yield 'vertical tab' => ["a\x0bb", '"a\\u000bb"'];
        yield 'form feed' => ["a\x0cb", '"a\\u000cb"'];
        yield 'escape char' => ["a\x1bb", '"a\\u001bb"'];
        yield 'unit separator' => ["a\x1fb", '"a\\u001fb"'];

        // Leading/trailing whitespace — quoted
        yield 'leading space' => [' x', '" x"'];
        yield 'trailing space' => ['x ', '"x "'];

        // Reserved words as strings — quoted
        yield 'string true' => ['true', '"true"'];
        yield 'string false' => ['false', '"false"'];
        yield 'string null' => ['null', '"null"'];

        // Numeric-looking strings — quoted
        yield 'numeric zero' => ['0', '"0"'];
        yield 'numeric int' => ['42', '"42"'];
        yield 'numeric negative' => ['-5', '"-5"'];
        yield 'numeric float' => ['3.14', '"3.14"'];
        yield 'numeric zero float' => ['0.5', '"0.5"'];
        yield 'numeric exp lower' => ['1e-6', '"1e-6"'];
        yield 'numeric exp upper' => ['1E10', '"1E10"'];
        yield 'numeric neg zero' => ['-0', '"-0"'];

        // Leading-zero numeric-like — quoted
        yield 'leading zero int' => ['0123', '"0123"'];
        yield 'leading zero neg' => ['-007', '"-007"'];

        // Leading hyphen (non-numeric) — quoted
        yield 'lone hyphen' => ['-', '"-"'];
        yield 'hyphen word' => ['-text', '"-text"'];
    }

    public function testEncodeTableEmpty(): void
    {
        $output = ToonUtils::encodeTable('issues', ['file', 'line'], []);
        $this->assertSame('issues[0]{file,line}:', $output);
    }

    public function testEncodeTableSingleRow(): void
    {
        $expected = <<<'EOF'
        issues[1]{file,line,severity}:
          src/Foo.php,10,error
        EOF;

        $output = ToonUtils::encodeTable(
            'issues',
            ['file', 'line', 'severity'],
            [
                ['file' => 'src/Foo.php', 'line' => 10, 'severity' => 'error'],
            ],
        );

        $this->assertSame($expected, $output);
    }

    public function testEncodeTableMultipleRows(): void
    {
        $expected = <<<'EOF'
        items[3]{a,b}:
          1,x
          2,y
          3,z
        EOF;

        $output = ToonUtils::encodeTable(
            'items',
            ['a', 'b'],
            [
                ['a' => 1, 'b' => 'x'],
                ['a' => 2, 'b' => 'y'],
                ['a' => 3, 'b' => 'z'],
            ],
        );

        $this->assertSame($expected, $output);
    }

    public function testEncodeTableRowWithQuotingTriggers(): void
    {
        $expected = <<<'EOF'
        items[1]{msg,note}:
          "needs, comma","has \"quote\""
        EOF;

        $output = ToonUtils::encodeTable(
            'items',
            ['msg', 'note'],
            [
                ['msg' => 'needs, comma', 'note' => 'has "quote"'],
            ],
        );

        $this->assertSame($expected, $output);
    }

    public function testEncodeTableMissingFieldDefaultsToNull(): void
    {
        $expected = <<<'EOF'
        items[1]{a,b}:
          hello,null
        EOF;

        $output = ToonUtils::encodeTable(
            'items',
            ['a', 'b'],
            [
                ['a' => 'hello'],
            ],
        );

        $this->assertSame($expected, $output);
    }
}
