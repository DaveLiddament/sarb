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
class Type
{
    /**
     * @var string
     */
    private $type;

    /**
     * Type constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return true if equal.
     *
     * @param Type $type
     *
     * @return bool
     */
    public function isEqual(self $type): bool
    {
        return $this->type === $type->getType();
    }
}
