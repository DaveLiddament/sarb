<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\Utils;

class ArrayUtils
{
    /**
     * Gets string value for given key in the array.
     *
     * @param array $array
     * @param string $key
     *
     * @throws ArrayParseException
     *
     * @return string
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
     * @param array $array
     * @param string $key
     *
     * @throws ArrayParseException
     *
     * @return int
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
     * Gets array value for given key in the array.
     *
     * @param array $array
     * @param string $key
     *
     * @throws ArrayParseException
     *
     * @return array
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
     * @param array $array
     * @param string $key
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
     * @param mixed $entity
     *
     * @throws ArrayParseException
     */
    public static function assertArray($entity): void
    {
        if (!is_array($entity)) {
            throw ArrayParseException::invalidType('base level', 'array');
        }
    }
}
