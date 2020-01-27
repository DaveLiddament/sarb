<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

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
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function isEqual(?self $fileName): bool
    {
        if (null === $fileName) {
            return false;
        }

        return $this->fileName === $fileName->getFileName();
    }
}
