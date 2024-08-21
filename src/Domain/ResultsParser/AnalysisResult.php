<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;

/**
 * Holds a single result from the static analysis results.
 */
final class AnalysisResult
{
    /**
     * AnalysisResult constructor.
     *
     * NOTE: $fullDetails should contain an array with all data from the original tool.
     * This allows tool specific output formatters to be written to output additional information if needed.
     * E.g. PHP-CS gives additional fields e.g. is_fixable. If this data needs to be shown to end user then
     * then a custom output formatter could be written to give all this additional information.
     *
     * @psalm-param array<mixed> $fullDetails
     */
    public function __construct(
        private Location $location,
        private Type $type,
        private string $message,
        /**
         * @psalm-var array<mixed>
         */
        private array $fullDetails,
        private Severity $severity,
    ) {
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @psalm-return array<mixed>
     */
    public function getFullDetails(): array
    {
        return $this->fullDetails;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSeverity(): Severity
    {
        return $this->severity;
    }

    public function asBaseLineAnalysisResult(): BaseLineAnalysisResult
    {
        return BaseLineAnalysisResult::make(
            $this->location->getRelativeFileName(),
            $this->location->getLineNumber(),
            $this->type,
            $this->message,
            $this->severity,
        );
    }
}
