<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common;

/**
 * Represents the full path of a file relative to the root of the repository.
 */
class FileName
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * FileName constructor.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function isEqual(self $fileName): bool
    {
        return $this->fileName === $fileName->getFileName();
    }
}
