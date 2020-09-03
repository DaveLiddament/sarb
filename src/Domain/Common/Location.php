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

class Location
{
    /**
     * @var FileName
     */
    private $fileName;

    /**
     * @var LineNumber
     */
    private $lineNumber;

    public static function fromAbsoluteFileName(
        AbsoluteFileName $absoluteFileName,
        ProjectRoot $projectRoot,
        LineNumber $lineNumber
    ): self {
        $relativeFileName = $projectRoot->getPathRelativeToRootDirectory($absoluteFileName->getFileName());

        return new self(new FileName($relativeFileName), $lineNumber);
    }

    /**
     * @deprecated use named constructor
     */
    public function __construct(FileName $fileName, LineNumber $lineNumber)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
    }

    public function getFileName(): FileName
    {
        return $this->fileName;
    }

    public function getLineNumber(): LineNumber
    {
        return $this->lineNumber;
    }

    /**
     * Used for ordering Locations, first by FileName then by line number.
     */
    public function compareTo(self $other): int
    {
        if ($this->fileName->getFileName() !== $other->fileName->getFileName()) {
            return $this->fileName->getFileName() <=> $other->fileName->getFileName();
        }

        return $this->lineNumber->getLineNumber() <=> $other->lineNumber->getLineNumber();
    }
}
