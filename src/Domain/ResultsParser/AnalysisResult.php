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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;

/**
 * Holds a single result from the static analysis results.
 */
class AnalysisResult
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $fullDetails;

    /**
     * AnalysisResult constructor.
     *
     * NOTE: $fullDetails should be a serialised version of the violation containing all the details that the
     * static analysis tool provided. It must be possible to reproduce the original violation from this string
     */
    public function __construct(Location $location, Type $type, string $message, string $fullDetails)
    {
        $this->location = $location;
        $this->type = $type;
        $this->message = $message;
        $this->fullDetails = $fullDetails;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getFullDetails(): string
    {
        return $this->fullDetails;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function asBaseLineAnalysisResult(): BaseLineAnalysisResult
    {
        return BaseLineAnalysisResult::make(
            $this->location->getFileName(),
            $this->location->getLineNumber(),
            $this->type,
            $this->message
        );
    }
}
