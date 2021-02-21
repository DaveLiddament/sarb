<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Webmozart\Assert\Assert;

class CliConfigReader
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

        // If nothing from input stream use STDIN instead.
        $inputSteam = $inputSteam ?? \STDIN;

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
