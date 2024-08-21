<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;

class BaseLineAnalysisResult
{
    private const LINE_NUMBER = 'lineNumber';
    private const FILE_NAME = 'fileName';
    private const TYPE = 'type';
    private const MESSAGE = 'message';
    private const SEVERITY = 'severity';

    /**
     * @psalm-param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function fromArray(array $array): self
    {
        $lineNumber = new LineNumber(ArrayUtils::getIntValue($array, self::LINE_NUMBER));
        $fileName = new RelativeFileName(ArrayUtils::getStringValue($array, self::FILE_NAME));
        $type = new Type(ArrayUtils::getStringValue($array, self::TYPE));
        $severity = Severity::fromStringOrNull(ArrayUtils::getOptionalStringValue($array, self::SEVERITY));
        $message = ArrayUtils::getStringValue($array, self::MESSAGE);

        return new self($fileName, $lineNumber, $type, $message, $severity);
    }

    public static function make(
        RelativeFileName $fileName,
        LineNumber $lineNumber,
        Type $type,
        string $message,
        Severity $severity,
    ): self {
        return new self($fileName, $lineNumber, $type, $message, $severity);
    }

    private function __construct(
        private RelativeFileName $fileName,
        private LineNumber $lineNumber,
        private Type $type,
        private string $message,
        private Severity $severity,
    ) {
    }

    public function getFileName(): RelativeFileName
    {
        return $this->fileName;
    }

    public function getLineNumber(): LineNumber
    {
        return $this->lineNumber;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSeverity(): Severity
    {
        return $this->severity;
    }

    /**
     * @psalm-return array<string,string|int>
     */
    public function asArray(): array
    {
        return [
            self::LINE_NUMBER => $this->getLineNumber()->getLineNumber(),
            self::FILE_NAME => $this->getFileName()->getFileName(),
            self::TYPE => $this->type->getType(),
            self::MESSAGE => $this->message,
            self::SEVERITY => $this->severity->getSeverity(),
        ];
    }

    /**
     * Return true if this matches given FileName, LineNumber and type.
     */
    public function isMatch(PreviousLocation $location, Type $type): bool
    {
        return
            $this->fileName->isEqual($location->getRelativeFileName())
            && $this->lineNumber->isEqual($location->getLineNumber())
            && $this->type->isEqual($type);
    }
}
