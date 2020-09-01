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

use Webmozart\Assert\Assert;

class PreviousLocation
{
    /**
     * @var FileName|null
     */
    private $fileName;
    /**
     * @var LineNumber|null
     */
    private $lineNumber;

    public static function noPreviousLocation(): self
    {
        return new self(null, null);
    }

    public static function fromFileNameAndLineNumber(FileName $fileName, LineNumber $lineNumber): self
    {
        return new self($fileName, $lineNumber);
    }

    private function __construct(?FileName $fileName, ?LineNumber $lineNumber)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
    }

    public function isNoPreviousLocation(): bool
    {
        return null === $this->fileName;
    }

    public function getFileName(): FileName
    {
        Assert::notNull($this->fileName, 'Trying to get FileName when PreviousLocation is not set');

        return $this->fileName;
    }

    public function getLineNumber(): LineNumber
    {
        Assert::notNull($this->lineNumber, 'Trying to get LineNumber when PreviousLocation is not set');

        return $this->lineNumber;
    }
}
