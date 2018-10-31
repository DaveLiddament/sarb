<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\Common;

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

    /**
     * Location constructor.
     *
     * @param FileName $fileName
     * @param LineNumber $lineNumber
     */
    public function __construct(FileName $fileName, LineNumber $lineNumber)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
    }

    /**
     * @return FileName
     */
    public function getFileName(): FileName
    {
        return $this->fileName;
    }

    /**
     * @return LineNumber
     */
    public function getLineNumber(): LineNumber
    {
        return $this->lineNumber;
    }

    /**
     * Returns true if $other refers to same location as this Location object.
     *
     * @param Location $other
     *
     * @return bool
     */
    public function isEqual(self $other): bool
    {
        return $this->fileName->isEqual($other->getFileName()) && $this->lineNumber->isEqual($other->getLineNumber());
    }
}
