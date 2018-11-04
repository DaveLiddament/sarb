<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal;

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
     * @param string $line
     *
     * @throws DiffParseException
     */
    public function __construct(string $line)
    {
        $matches = [];
        $match = preg_match('/^@@ -(\d+),(\d+) \+(\d+),(\d+) @@/', $line, $matches);

        if (1 !== $match) {
            throw DiffParseException::invalidRangeInformation($line);
        }

        $this->originalFileStartLine = (int) $matches[1];
        $this->originalFileHunkSize = (int) $matches[2];
        $this->newFileStartLine = (int) $matches[3];
        $this->newFileHunkSize = (int) $matches[4];
    }

    /**
     * @return int
     */
    public function getOriginalFileStartLine(): int
    {
        return $this->originalFileStartLine;
    }

    /**
     * @return int
     */
    public function getOriginalFileHunkSize(): int
    {
        return $this->originalFileHunkSize;
    }

    /**
     * @return int
     */
    public function getNewFileStartLine(): int
    {
        return $this->newFileStartLine;
    }

    /**
     * @return int
     */
    public function getNewFileHunkSize(): int
    {
        return $this->newFileHunkSize;
    }
}
