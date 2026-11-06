<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Webmozart\Assert\Assert;

final class CliConfigReader
{
    public static function getArgument(InputInterface $input, string $argumentName): string
    {
        $value = $input->getArgument($argumentName);
        Assert::string($value);

        return $value;
    }

    public static function getOption(InputInterface $input, string $optionName): ?string
    {
        $value = $input->getOption($optionName);

        if (null === $value) {
            return null;
        }

        Assert::string($value);

        return $value;
    }

    public static function getOptionWithDefaultValue(InputInterface $input, string $optionName): string
    {
        $value = self::getOption($input, $optionName);
        Assert::notNull($value);

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

        if (null === $inputSteam) {
            // @codeCoverageIgnoreStart
            // Not possible to cover in tests: CommandTester always supplies an input stream.
            // Reading STDIN from an interactive terminal would block forever waiting for input.
            if (stream_isatty(\STDIN)) {
                throw new SarbException('No static analysis results provided. Pipe the output of the static analysis tool into sarb.');
            }

            // If nothing from input stream use STDIN instead.
            $inputSteam = \STDIN;
            // @codeCoverageIgnoreEnd
        }

        $input = stream_get_contents($inputSteam);

        if (false === $input) {
            // No way of easily testing this
            throw new SarbException('Can not read input stream'); // @codeCoverageIgnore
        }

        return $input;
    }

    public static function getBooleanOption(InputInterface $input, string $argument): bool
    {
        $option = $input->getOption($argument);
        Assert::boolean($option);

        return $option;
    }
}
