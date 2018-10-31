<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser;

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\File\FileImportException;
use DaveLiddament\StaticAnalysisBaseliner\Core\File\FileReader;
use DaveLiddament\StaticAnalysisBaseliner\Core\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisBaseliner\Core\Utils\ParseAtLocationException;

/**
 * Imports a AnalysisResults from a file on disk.
 */
class Importer
{
    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * Importer constructor.
     *
     * @param FileReader $fileReader
     */
    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * Imports AnalysisResults from $fileName.
     *
     * @param StaticAnalysisResultsParser $resultsParser
     * @param FileName $fileName
     *
     * @throws FileImportException
     *
     * @return AnalysisResults
     */
    public function importFromFile(
        StaticAnalysisResultsParser $resultsParser,
        FileName $fileName
    ): AnalysisResults {
        try {
            $fileContents = $this->fileReader->readFile($fileName);

            return $resultsParser->convertFromString($fileContents);
        } catch (InvalidFileFormatException | ParseAtLocationException $e) {
            throw new FileImportException($resultsParser->getIdentifier()->getCode(), $fileName, $e->getMessage());
        }
    }
}
