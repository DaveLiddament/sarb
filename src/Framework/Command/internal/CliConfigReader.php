<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use _HumbugBoxa35debbd0202\Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;

class CliConfigReader
{
    /**
     * @throws InvalidConfigException
     */
    public static function getArgument(InputInterface $input, string $argumentName): string
    {
        $value = $input->getArgument($argumentName);

        if (is_string($value)) {
            return $value;
        }

        // Should never happen. Configured option should only return string or null.
        throw new LogicException("Incorrectly configured option [$argumentName]"); // @codeCoverageIgnore
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getOption(InputInterface $input, string $optionName): ?string
    {
        $value = $input->getOption($optionName);

        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        // Should never happen. Configured option should only return string or null.
        throw new LogicException("Incorrectly configured option [$optionName]"); // @codeCoverageIgnore
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getOptionWithDefaultValue(InputInterface $input, string $optionName): string
    {
        $value = self::getOption($input, $optionName);
        if (null === $value) {
            // Should never happen. Configured option should always have a default value.
            throw new LogicException("Incorreclty configured option. No default value set for option {$optionName}"); // @codeCoverageIgnore
        }

        return $value;
    }
}
