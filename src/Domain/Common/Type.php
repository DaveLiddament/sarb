<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

/**
 * Represents type of static analysis violation.
 */
final class Type
{
    /**
     * Type constructor.
     */
    public function __construct(
        private string $type,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return true if equal.
     */
    public function isEqual(self $type): bool
    {
        return $this->type === $type->getType();
    }
}
