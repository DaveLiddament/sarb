<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

/**
 * Holds how many of the results' types came from identifiers provided by the static analysis tool
 * (as opposed to types guessed from the violation message).
 */
final class TypeIdentifiersUsage
{
    private const NONE = 'none';
    private const SOME = 'some';
    private const ALL = 'all';

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public static function some(): self
    {
        return new self(self::SOME);
    }

    public static function all(): self
    {
        return new self(self::ALL);
    }

    /**
     * NONE for null. Any unrecognised string is treated as SOME for forward compatibility.
     */
    public static function fromStringOrNull(?string $usage): self
    {
        if (null === $usage) {
            return self::none();
        }

        if (self::ALL === $usage) {
            return self::all();
        }

        return self::some();
    }

    private function __construct(
        private string $usage,
    ) {
    }

    /**
     * Returns null for NONE (so the key can be omitted when serialising).
     */
    public function asStringOrNull(): ?string
    {
        return self::NONE === $this->usage ? null : $this->usage;
    }

    public function isFromToolIdentifiers(): bool
    {
        return self::NONE !== $this->usage;
    }

    public function isAllFromToolIdentifiers(): bool
    {
        return self::ALL === $this->usage;
    }
}
