<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

final class LineTypeDetector
{
    private const FILE_DIFF_START = 'diff --git';
    private const CHANGE_HUNK_START = '@@';
    private const FILE_DELETED = '+++ /dev/null';
    private const FILE_ADDED = '--- /dev/null';

    public static function isStartOfFileDiff(string $line): bool
    {
        return StringUtils::startsWith(self::FILE_DIFF_START, $line);
    }

    public static function isDeletedFile(string $line): bool
    {
        return StringUtils::startsWith(self::FILE_DELETED, $line);
    }

    public static function isFileAdded(string $line): bool
    {
        return StringUtils::startsWith(self::FILE_ADDED, $line);
    }

    public static function isStartOfChangeHunk(string $line): bool
    {
        return StringUtils::startsWith(self::CHANGE_HUNK_START, $line);
    }
}
