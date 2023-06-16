<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;

class JsonUtils
{
    /**
     * Returns JSON string as an associative array representation.
     *
     * @throws InvalidContentTypeException
     *
     * @psalm-return array<mixed>
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
     * @psalm-param array<mixed> $data
     */
    public static function toString(array $data): string
    {
        $asString = json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);
        if (false === $asString) {
            throw new \LogicException('Can not convert data to JSON string');
        }

        return $asString;
    }
}
