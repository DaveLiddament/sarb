<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsImportException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ErrorReporter
{
    public static function reportError(OutputInterface $output, Throwable $throwable): int
    {
        try {
            throw $throwable;
        } catch (InvalidConfigException $e) {
            self::writeToStdError($output, $e->getMessage());

            return 11;
        } catch (BaseLineImportException $e) {
            self::writeToStdError($output, $e->getMessage());

            return 12;
        } catch (AnalysisResultsImportException $e) {
            self::writeToStdError($output, $e->getMessage());

            return 13;
        } catch (FileAccessException $e) {
            self::writeToStdError($output, $e->getMessage());

            return 14;
        } catch (HistoryAnalyserException $e) {
            self::writeToStdError($output, $e->getMessage());

            return 15;
        } catch (Throwable $e) {
            // This should never happen. All exceptions should extend SarbException
            self::writeToStdError($output, "Unexpected critical error: {$e->getMessage()}");

            return 100;
        }
    }

    public static function writeToStdError(OutputInterface $output, string $message): void
    {
        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $errorOutput->writeln("<error>$message</error>");
    }
}
