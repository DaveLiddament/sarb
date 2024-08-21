<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

/**
 * Used for invalid user config provided.
 */
final class InvalidConfigException extends \Exception
{
    /**
     * @param string[] $validOptions
     */
    public static function invalidOptionValue(string $option, string $value, array $validOptions): self
    {
        $message = sprintf(
            'Invalid value [%s] for option [%s]. Pick one of: %s',
            $value,
            $option,
            implode('|', $validOptions),
        );

        return new self($message);
    }
}
