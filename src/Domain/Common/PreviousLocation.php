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

use Webmozart\Assert\Assert;

class PreviousLocation
{
    /**
     * @var Location|null
     */
    private $location;

    public static function noPreviousLocation(): self
    {
        return new self(null);
    }

    public static function fromLocation(Location $location): self
    {
        return new self($location);
    }

    private function __construct(?Location $location)
    {
        $this->location = $location;
    }

    public function isNoPreviousLocation(): bool
    {
        return null === $this->location;
    }

    public function getLocation(): Location
    {
        Assert::notNull($this->location, 'Trying to get Location when PreviousLocation is not set');

        return $this->location;
    }
}
