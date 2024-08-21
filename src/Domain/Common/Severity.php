<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use Webmozart\Assert\Assert;

final class Severity
{
    public const WARNING = 'warning';
    public const ERROR = 'error';

    /**
     * @var string
     */
    private $severity;

    public static function fromStringOrNull(?string $severity): self
    {
        if (null === $severity) {
            return new self(self::ERROR);
        }

        return new self($severity);
    }

    public static function error(): self
    {
        return new self(self::ERROR);
    }

    public static function warning(): self
    {
        return new self(self::WARNING);
    }

    private function __construct(string $severity)
    {
        Assert::true(self::isValueValid($severity), "Invalid severity: $severity");
        $this->severity = $severity;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public static function isValueValid(string $severity): bool
    {
        return in_array($severity, [self::ERROR, self::WARNING], true);
    }

    public function isWarning(): bool
    {
        return self::WARNING === $this->severity;
    }
}
