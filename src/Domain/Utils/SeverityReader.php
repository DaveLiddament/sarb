<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;

final class SeverityReader
{
    /**
     * @param array<mixed> $data
     *
     * @throws ArrayParseException
     */
    public static function getOptionalSeverity(array $data, string $key): Severity
    {
        $severityAsStringOrNull = ArrayUtils::getOptionalStringValue($data, $key);
        if (null === $severityAsStringOrNull) {
            return Severity::error();
        }

        return self::getMandatorySeverity($data, $key);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws ArrayParseException
     */
    public static function getMandatorySeverity(array $data, string $key): Severity
    {
        $severityAsString = ArrayUtils::getStringValue($data, $key);
        $lowerCaseSeverity = strtolower($severityAsString);
        if (!Severity::isValueValid($lowerCaseSeverity)) {
            throw ArrayParseException::invalidValue($key, $severityAsString);
        }

        return Severity::fromStringOrNull($lowerCaseSeverity);
    }
}
