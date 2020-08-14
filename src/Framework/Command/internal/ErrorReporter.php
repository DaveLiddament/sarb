<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileImportException;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ErrorReporter
{
    public static function reportError(OutputInterface $output, Throwable $throwable): int
    {
        try {
            throw $throwable;
        } catch (InvalidConfigException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 2;
        } catch (FileImportException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 3;
        } catch (SarbException $e) {
            $output->writeln("<error>Something went wrong: {$e->getMessage()}");

            return 4;
        } catch (Throwable $e) {
            // This should never happen. All exceptions should extend SarbException
            $output->writeln("<error>Unexpected critical error: {$e->getMessage()}</error>");

            return 5;
        }
    }
}
