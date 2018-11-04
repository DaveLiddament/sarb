<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ArrayUtils;

/**
 * Holds a single result from the static analysis results.
 */
class AnalysisResult
{
    const LINE_NUMBER = 'lineNumber';
    const FILE_NAME = 'fileName';
    const TYPE = 'type';
    const FULL_DETAILS = 'fullDetails';

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
    private $fullDetails;

    /**
     * AnalysisResult constructor.
     *
     * @param Location $location
     * @param Type $type
     * @param string $fullDetails
     */
    public function __construct(Location $location, Type $type, string $fullDetails)
    {
        $this->location = $location;
        $this->type = $type;
        $this->fullDetails = $fullDetails;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFullDetails(): string
    {
        return $this->fullDetails;
    }

    /**
     * Return true if AnalysisResult matches given FileName, LineNumber and type.
     *
     * @param Location $location
     * @param Type $type
     *
     * @return bool
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
            self::FULL_DETAILS => $this->getFullDetails(),
        ];
    }

    /**
     * @param array $array
     *
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

        return new self($location, $type, ArrayUtils::getStringValue($array, self::FULL_DETAILS));
    }
}
