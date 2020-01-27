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

class JsonUtils
{
    /**
     * Returns JSON string as an associative array representation.
     *
     * @throws JsonParseException
     */
    public static function toArray(string $jsonAsString): array
    {
        /** @psalm-suppress MixedAssignment */
        $asArray = json_decode($jsonAsString, true);
        if (!is_array($asArray)) {
            throw new JsonParseException();
        }

        return $asArray;
    }

    /**
     * Converts array to a JSON representation in a string.
     *
     * @throws JsonParseException
     */
    public static function toString(array $data): string
    {
        $asString = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (false === $asString) {
            throw new JsonParseException();
        }

        return $asString;
    }
}
