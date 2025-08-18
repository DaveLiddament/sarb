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

final class ArrayUtils
{
    /**
     * Gets string value for given key in the array.
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function getStringValue(array $array, string $key): string
    {
        self::assertArrayKeyExists($array, $key);
        if (is_string($array[$key])) {
            return $array[$key];
        }
        throw ArrayParseException::invalidType($key, 'string');
    }

    /**
     * Gets int value for given key in the array.
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function getIntValue(array $array, string $key): int
    {
        self::assertArrayKeyExists($array, $key);
        if (is_int($array[$key])) {
            return $array[$key];
        }
        throw ArrayParseException::invalidType($key, 'int');
    }

    /**
     * Gets int value for given key in the array.
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function getIntOrNullValue(array $array, string $key): ?int
    {
        self::assertArrayKeyExists($array, $key);
        if (null === $array[$key]) {
            return null;
        }
        if (is_int($array[$key])) {
            return $array[$key];
        }
        throw ArrayParseException::invalidType($key, 'int');
    }

    /**
     * Gets optional value. Note: if key exists, then the value must be a string.
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function getOptionalStringValue(array $array, string $key): ?string
    {
        if (!array_key_exists($key, $array)) {
            return null;
        }

        if (is_string($array[$key])) {
            return $array[$key];
        }
        throw ArrayParseException::invalidType($key, 'string');
    }

    /**
     * Gets array value for given key in the array.
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     *
     * @return array<mixed> $array
     */
    public static function getArrayValue(array $array, string $key): array
    {
        self::assertArrayKeyExists($array, $key);
        if (is_array($array[$key])) {
            return $array[$key];
        }
        throw ArrayParseException::invalidType($key, 'array');
    }

    /**
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    private static function assertArrayKeyExists(array $array, string $key): void
    {
        if (!array_key_exists($key, $array)) {
            throw ArrayParseException::missingKey($key);
        }
    }

    /**
     * @psalm-assert array $entity
     *
     * @throws ArrayParseException
     */
    public static function assertArray(mixed $entity): void
    {
        if (!is_array($entity)) {
            throw ArrayParseException::invalidType('base level', 'array');
        }
    }

    /**
     * @psalm-assert array<array-key,string> $array
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function assertArrayOfStrings(array $array): void
    {
        foreach ($array as $key => $value) {
            if (!is_string($value)) {
                throw ArrayParseException::invalidType((string) $key, 'string');
            }
        }
    }

    /**
     * Extracts a integer value from an array, however the integer is in string format.
     *
     * e.g. Assume the following code:
     *
     *   $array = ['age' => '21'];
     *   $age = ArrayUtils::getIntAsStringValue($array, 'age');
     *
     * $age would be the integer value 21.
     *
     * @param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function getIntAsStringValue(array $array, string $key): int
    {
        self::assertArrayKeyExists($array, $key);
        if (is_string($array[$key])) {
            $valueAsString = $array[$key];
            $valueAsInt = (int) $valueAsString;
            $intValueAsString = (string) $valueAsInt;
            if ($intValueAsString === $valueAsString) {
                return $valueAsInt;
            }
        }
        throw ArrayParseException::invalidType($key, 'int as string');
    }
}
