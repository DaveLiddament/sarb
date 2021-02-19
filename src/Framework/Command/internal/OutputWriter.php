<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutputWriter
{
    /**
     * All information output is written to StdErr using this method.
     */
    public static function writeToStdError(OutputInterface $output, string $message, bool $isError): void
    {
        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $formattedMessage = $isError ? "<error>$message</error>" : "<info>$message</info>";
        $errorOutput->writeln("$formattedMessage");
    }
}
