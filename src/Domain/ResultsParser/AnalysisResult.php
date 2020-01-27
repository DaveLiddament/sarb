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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;

/**
 * Holds a single result from the static analysis results.
 */
class AnalysisResult
{
    private const LINE_NUMBER = 'lineNumber';
    private const FILE_NAME = 'fileName';
    private const TYPE = 'type';
    private const MESSAGE = 'message';
    private const FULL_DETAILS = 'fullDetails';

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

    /**
     * Return true if AnalysisResult matches given FileName, LineNumber and type.
     */
    public function isMatch(Location $location, Type $type): bool
    {
        return $this->location->isEqual($location) && $this->type->isEqual($type);
    }

    public function asArray(): array
    {
        return [
            self::LINE_NUMBER => $this->location->getLineNumber()->getLineNumber(),
            self::FILE_NAME => $this->location->getFileName()->getFileName(),
            self::TYPE => $this->type->getType(),
            self::MESSAGE => $this->message,
            self::FULL_DETAILS => $this->getFullDetails(),
        ];
    }

    /**
     * @throws ArrayParseException
     *
     * @return AnalysisResult
     */
    public static function fromArray(array $array): self
    {
        $lineNumber = new LineNumber(ArrayUtils::getIntValue($array, self::LINE_NUMBER));
        $fileName = new FileName(ArrayUtils::getStringValue($array, self::FILE_NAME));
        $type = new Type(ArrayUtils::getStringValue($array, self::TYPE));
        $location = new Location($fileName, $lineNumber);
        $message = ArrayUtils::getStringValue($array, self::MESSAGE);
        $fullDetails = ArrayUtils::getStringValue($array, self::FULL_DETAILS);

        return new self($location, $type, $message, $fullDetails);
    }
}
