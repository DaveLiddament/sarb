<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsImportException;
use Symfony\Component\Console\Output\OutputInterface;

class ErrorReporter
{
    public static function reportError(OutputInterface $output, \Throwable $throwable): int
    {
        try {
            throw $throwable;
        } catch (InvalidConfigException $e) {
            OutputWriter::writeToStdError($output, $e->getMessage(), true);

            return 11;
        } catch (BaseLineImportException $e) {
            OutputWriter::writeToStdError($output, $e->getMessage(), true);

            return 12;
        } catch (AnalysisResultsImportException $e) {
            OutputWriter::writeToStdError($output, $e->getMessage(), true);

            return 13;
        } catch (FileAccessException $e) {
            OutputWriter::writeToStdError($output, $e->getMessage(), true);

            return 14;
        } catch (HistoryAnalyserException $e) {
            OutputWriter::writeToStdError($output, $e->getMessage(), true);

            return 15;
        } catch (\Throwable $e) {
            // This should never happen. All exceptions should extend SarbException
            OutputWriter::writeToStdError($output, "Unexpected critical error: {$e->getMessage()}", true);
            OutputWriter::writeToStdError($output, $e->getTraceAsString(), true);

            return 100;
        }
    }
}
