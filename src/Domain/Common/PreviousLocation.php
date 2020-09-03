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
     * @var RelativeFileName|null
     */
    private $relativeFileName;
    /**
     * @var LineNumber|null
     */
    private $lineNumber;

    public static function noPreviousLocation(): self
    {
        return new self(null, null);
    }

    public static function fromFileNameAndLineNumber(RelativeFileName $relativeFileName, LineNumber $lineNumber): self
    {
        return new self($relativeFileName, $lineNumber);
    }

    private function __construct(?RelativeFileName $relativeFileName, ?LineNumber $lineNumber)
    {
        $this->relativeFileName = $relativeFileName;
        $this->lineNumber = $lineNumber;
    }

    public function isNoPreviousLocation(): bool
    {
        return null === $this->relativeFileName;
    }

    public function getRelativeFileName(): RelativeFileName
    {
        Assert::notNull($this->relativeFileName, 'Trying to get FileName when PreviousLocation is not set');

        return $this->relativeFileName;
    }

    public function getLineNumber(): LineNumber
    {
        Assert::notNull($this->lineNumber, 'Trying to get LineNumber when PreviousLocation is not set');

        return $this->lineNumber;
    }
}
