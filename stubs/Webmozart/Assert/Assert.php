<?php

namespace Webmozart\Assert;

class Assert
{
    /**
     * @psalm-assert true $value
     */
    public static function true($value, $message = '') {}


    /**
     * @psalm-assert !null $value
     */
    public static function notNull($value, $message = '') {}


    /**
     * @template T
     * @template-typeof T $type
     * @param class-string $type
     *
     * TODO massive hack. Replace FQCN of GitCommit to T when a new version of psalm is ready that includes this fix: https://github.com/vimeo/psalm/issues/1044
     *
     * @psalm-assert DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit $value
     */
    public static function isInstanceOf($value, $type, $message = '') {}
}
