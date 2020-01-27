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

/**
 * Represents a line number (in a file).
 */
class LineNumber
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * LineNumber constructor.
     */
    public function __construct(int $lineNumber)
    {
        Assert::greaterThanEq($lineNumber, 0, 'Line number must be positive integer or 0. Got: %s');
        $this->lineNumber = $lineNumber;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function isEqual(self $lineNumber): bool
    {
        return $this->lineNumber === $lineNumber->getLineNumber();
    }
}
