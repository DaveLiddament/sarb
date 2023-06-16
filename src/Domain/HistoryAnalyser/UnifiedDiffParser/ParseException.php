<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\internal\DiffParseException;

class ParseException extends HistoryAnalyserException
{
    /**
     * @var string
     */
    public const UNEXPECTED_END_OF_FILE = 'Unexpected end of file';

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $reason;

    /**
     * Create from DiffParseException.
     */
    public static function fromDiffParseException(string $location, DiffParseException $e): self
    {
        return new self($location, $e->getReason(), $e->getDetails(), $e);
    }

    /**
     * ParseException constructor.
     */
    public function __construct(string $location, string $reason, string $details, ?\Throwable $previous)
    {
        $message = "Error parsing diff. Line {$location}. Reason: {$reason}. Details: [$details]";
        parent::__construct($message, 0, $previous);
        $this->location = $location;
        $this->reason = $reason;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
