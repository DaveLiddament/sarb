<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;

class BaseLineAnalysisResult
{
    private const LINE_NUMBER = 'lineNumber';
    private const FILE_NAME = 'fileName';
    private const TYPE = 'type';
    private const MESSAGE = 'message';

    /**
     * @var FileName
     */
    private $fileName;
    /**
     * @var LineNumber
     */
    private $lineNumber;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var string
     */
    private $message;

    /**
     * @psalm-param array<mixed> $array
     *
     * @throws ArrayParseException
     */
    public static function fromArray(array $array): self
    {
        $lineNumber = new LineNumber(ArrayUtils::getIntValue($array, self::LINE_NUMBER));
        $fileName = new FileName(ArrayUtils::getStringValue($array, self::FILE_NAME));
        $type = new Type(ArrayUtils::getStringValue($array, self::TYPE));
        $message = ArrayUtils::getStringValue($array, self::MESSAGE);

        return new self($fileName, $lineNumber, $type, $message);
    }

    public static function make(
        FileName $fileName,
        LineNumber $lineNumber,
        Type $type,
        string $message
    ): self {
        return new self($fileName, $lineNumber, $type, $message);
    }

    private function __construct(FileName $fileName, LineNumber $lineNumber, Type $type, string $message)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
        $this->type = $type;
        $this->message = $message;
    }

    public function getFileName(): FileName
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
        ];
    }

    /**
     * Return true if this matches matches given FileName, LineNumber and type.
     */
    public function isMatch(PreviousLocation $location, Type $type): bool
    {
        return
            $this->fileName->isEqual($location->getFileName()) &&
            $this->lineNumber->isEqual($location->getLineNumber()) &&
            $this->type->isEqual($type);
    }
}
