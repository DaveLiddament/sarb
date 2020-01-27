<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

class RangeInformation
{
    /**
     * @var int
     */
    private $originalFileStartLine;

    /**
     * @var int
     */
    private $originalFileHunkSize;

    /**
     * @var int
     */
    private $newFileStartLine;

    /**
     * @var int
     */
    private $newFileHunkSize;

    /**
     * RangeInformation constructor.
     *
     * @throws DiffParseException
     */
    public function __construct(string $line)
    {
        $matches = [];
        $match = preg_match('/^@@ -(\d+)(,\d+){0,1} \+(\d+)(,\d+){0,1} @@/', $line, $matches);

        if (1 !== $match) {
            throw DiffParseException::invalidRangeInformation($line);
        }

        $this->originalFileStartLine = (int) $matches[1];

        if ('' === $matches[2]) {
            $this->originalFileHunkSize = 1;
        } else {
            $this->originalFileHunkSize = (int) StringUtils::removeFromStart(',', $matches[2]);
        }
        $this->newFileStartLine = (int) $matches[3];

        if (5 === count($matches)) {
            $this->newFileHunkSize = (int) StringUtils::removeFromStart(',', $matches[4]);
        } else {
            $this->newFileHunkSize = 1;
        }
    }

    public function getOriginalFileStartLine(): int
    {
        return $this->originalFileStartLine;
    }

    public function getOriginalFileHunkSize(): int
    {
        return $this->originalFileHunkSize;
    }

    public function getNewFileStartLine(): int
    {
        return $this->newFileStartLine;
    }

    public function getNewFileHunkSize(): int
    {
        return $this->newFileHunkSize;
    }
}
