<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryMarkerException;

final class GitCommit implements HistoryMarker
{
    /**
     * @var string
     */
    private $gitSha;

    /**
     * @throws InvalidHistoryMarkerException
     */
    public function __construct(string $gitSha)
    {
        if (!self::validateGitSha($gitSha)) {
            throw InvalidHistoryMarkerException::invalidHistoryMarker("Invalid git SHA [$gitSha]");
        }
        $this->gitSha = $gitSha;
    }

    public function asString(): string
    {
        return $this->gitSha;
    }

    /**
     * Validates the string provided could be a valid git SHA.
     */
    public static function validateGitSha(string $gitSha): bool
    {
        return 1 === preg_match('/^[0-9a-f]{40}$/', $gitSha);
    }
}
