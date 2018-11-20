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

use Webmozart\Assert\Assert;

class StringUtils
{
    /**
     * Returns true if $haystack starts with $needle.
     *
     * @param string $needle
     * @param string $haystack
     *
     * @return bool
     */
    public static function startsWith(string $needle, string $haystack): bool
    {
        return 0 === strpos($haystack, $needle);
    }

    /**
     * Remove $needle from start of $haystack.
     *
     * @param string $needle
     * @param string $haystack
     *
     * @return string
     */
    public static function removeFromStart(string $needle, string $haystack): string
    {
        Assert::true(self::startsWith($needle, $haystack));
        $length = strlen($needle);

        return substr($haystack, $length);
    }

    /**
     * Returns true if $haystack ends with $needle.
     *
     * @param string $needle
     * @param string $haystack
     *
     * @return bool
     */
    public static function endsWith(string $needle, string $haystack): bool
    {
        $length = strlen($needle);

        return substr($haystack, -$length) === $needle;
    }
}
