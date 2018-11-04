<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal;

use Exception;

class DiffParseException extends Exception
{
    public const END_OF_FILE = '<EOF>';

    public static function missingRenameTo(string $line): self
    {
        return new self('NO_RENAME_TO', $line);
    }

    public static function missingNewFileName(string $line): self
    {
        return new self('NO_NEW_FILE_NAME', $line);
    }

    public static function invalidRangeInformation(string $rangeInformation): self
    {
        return new self('RANGE_INFORMATION_PARSE_ERROR', $rangeInformation);
    }

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $details;

    public function __construct(string $reason, string $details)
    {
        parent::__construct("$reason: $details");
        $this->reason = $reason;
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }
}
