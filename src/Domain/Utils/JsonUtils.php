<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;

final class JsonUtils
{
    /**
     * Returns JSON string as an associative array representation.
     *
     * @throws InvalidContentTypeException
     *
     * @return array<mixed>
     */
    public static function toArray(string $jsonAsString): array
    {
        /** @psalm-suppress MixedAssignment */
        $asArray = json_decode($jsonAsString, true);
        if (!is_array($asArray)) {
            throw InvalidContentTypeException::notJson();
        }

        return $asArray;
    }

    /**
     * Converts array to a JSON representation in a string.
     *
     * NOTE: invalid UTF-8 byte sequences (which some static analysis tools can emit in messages)
     * are replaced with the Unicode substitution character rather than failing.
     *
     * @param array<mixed> $data
     *
     * @throws SarbException
     */
    public static function toString(array $data): string
    {
        $asString = json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT | \JSON_INVALID_UTF8_SUBSTITUTE);
        if (false === $asString) {
            throw new SarbException('Can not convert data to JSON string');
        }

        return $asString;
    }
}
