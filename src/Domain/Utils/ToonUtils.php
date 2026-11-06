<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

final class ToonUtils
{
    /**
     * Encodes a homogeneous list of rows as a TOON tabular array.
     *
     * Output uses 2-space indentation, LF line endings, and no trailing newline,
     * as required by the TOON specification (https://github.com/toon-format/spec).
     *
     * @param list<string>                                  $fields ordered column names; each must match /^[A-Za-z_][A-Za-z0-9_.]*$/
     * @param list<array<string, int|bool|string|null>> $rows   each row keyed by field name
     */
    public static function encodeTable(string $key, array $fields, array $rows): string
    {
        $header = sprintf('%s[%d]{%s}:', $key, count($rows), implode(',', $fields));

        if ([] === $rows) {
            return $header;
        }

        $lines = [$header];
        foreach ($rows as $row) {
            $cells = [];
            foreach ($fields as $field) {
                $cells[] = self::encodeScalar($row[$field] ?? null);
            }
            $lines[] = '  '.implode(',', $cells);
        }

        return implode("\n", $lines);
    }

    public static function encodeScalar(int|bool|string|null $value): string
    {
        if (null === $value) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value)) {
            return (string) $value;
        }

        return self::needsQuoting($value) ? self::quote($value) : $value;
    }

    private static function needsQuoting(string $value): bool
    {
        if ('' === $value) {
            return true;
        }

        if (1 === preg_match('/[\x00-\x1F]/', $value)) {
            return true;
        }

        if (false !== strpbrk($value, ',:"\\[]{}')) {
            return true;
        }

        if (str_starts_with($value, ' ') || str_ends_with($value, ' ')) {
            return true;
        }

        if (in_array($value, ['true', 'false', 'null'], true)) {
            return true;
        }

        if (1 === preg_match('/^-?(0|[1-9]\d*)(\.\d+)?([eE][+-]?\d+)?$/', $value)) {
            return true;
        }

        if (1 === preg_match('/^-?0\d/', $value)) {
            return true;
        }

        return '-' === $value[0];
    }

    private static function quote(string $value): string
    {
        $replacements = [
            '\\' => '\\\\',
            '"' => '\\"',
            "\n" => '\\n',
            "\r" => '\\r',
            "\t" => '\\t',
        ];

        // Any remaining control character (U+0000–U+001F) MUST be escaped as \uXXXX.
        for ($codepoint = 0; $codepoint < 0x20; ++$codepoint) {
            $char = chr($codepoint);
            if (!array_key_exists($char, $replacements)) {
                $replacements[$char] = sprintf('\\u%04x', $codepoint);
            }
        }

        return '"'.strtr($value, $replacements).'"';
    }
}
