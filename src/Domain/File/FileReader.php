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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;

final class FileReader
{
    /**
     * Returns string containing contents of the file.
     *
     * @throws FileAccessException
     */
    public function readFile(FileName $fileName): string
    {
        $fileNameAsString = $fileName->getFileName();

        $fileContents = @file_get_contents($fileNameAsString);
        if (false === $fileContents) {
            throw FileAccessException::readFileException($fileName);
        }

        return $fileContents;
    }

    /**
     * Returns array representing the contents of the file. Assumes the file must be JSON.
     *
     * @return array<mixed>
     *
     * @throws FileAccessException
     * @throws InvalidContentTypeException
     */
    public function readJsonFile(FileName $fileName): array
    {
        $fileContents = $this->readFile($fileName);

        return JsonUtils::toArray($fileContents);
    }
}
