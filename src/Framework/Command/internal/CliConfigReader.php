<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;

class CliConfigReader
{
    public static function getArgument(InputInterface $input, string $argumentName): string
    {
        $value = $input->getArgument($argumentName);

        if (is_string($value)) {
            return $value;
        }

        // Should never happen. Configured option should only return string or null.
        throw new LogicException("Incorrectly configured option [$argumentName]"); // @codeCoverageIgnore
    }

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

    public static function getOptionWithDefaultValue(InputInterface $input, string $optionName): string
    {
        $value = self::getOption($input, $optionName);
        if (null === $value) {
            // Should never happen. Configured option should always have a default value.
            throw new LogicException("Incorrectly configured option. No default value set for option {$optionName}"); // @codeCoverageIgnore
        }

        return $value;
    }

    /**
     * Returns STDIN as a string.
     *
     * @throws SarbException
     */
    public static function getStdin(InputInterface $input): string
    {
        // If testing this will get input added by `CommandTester::setInputs` method.
        $inputSteam = ($input instanceof StreamableInputInterface) ? $input->getStream() : null;

        // If nothing from input stream use STDIN instead.
        $inputSteam = $inputSteam ?? \STDIN;

        $input = stream_get_contents($inputSteam);

        if (false === $input) {
            // No way of easily testing this
            throw new SarbException('Can not read input stream'); // @codeCoverageIgnore
        }

        return $input;
    }
}
