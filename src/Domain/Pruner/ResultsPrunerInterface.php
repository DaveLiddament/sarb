<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;

interface ResultsPrunerInterface
{
    /**
     * Returns results with the baseline issues removed from them.
     */
    public function getPrunedResults(
        BaseLineFileName $baseLineFileName,
        string $analysisResults,
        ProjectRoot $projectRoot,
        bool $ignoreWarnings,
    ): PrunedResults;
}
