<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;

class FileReader
{
    /**
     * Returns string containing contents of the file.
     *
     * @throws FileAccessException
     */
    public function readFile(FileName $fileName): string
    {
        $fileNameAsString = $fileName->getFileName();
        if (!file_exists($fileNameAsString)) {
            throw FileAccessException::readFileException();
        }

        $fileContents = file_get_contents($fileNameAsString);
        if (false === $fileContents) {
            throw FileAccessException::readFileException();
        }

        return $fileContents;
    }

    /**
     * Returns array representing the contents of the file. Assumes the file must be JSON.
     *
     * @throws JsonParseException
     * @throws FileAccessException
     */
    public function readJsonFile(FileName $fileName): array
    {
        $fileContents = $this->readFile($fileName);

        return JsonUtils::toArray($fileContents);
    }
}
