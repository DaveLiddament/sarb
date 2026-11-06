<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;

interface ResultsPrunerInterface
{
    /**
     * Returns results with the baseline issues removed from them.
     *
     * @throws InputMissingTypeIdentifiersException if the baseline holds types from tool provided
     *                                              identifiers but the supplied results contain none
     */
    public function getPrunedResults(
        BaseLineFileName $baseLineFileName,
        string $analysisResults,
        ProjectRoot $projectRoot,
        bool $ignoreWarnings,
    ): PrunedResults;
}
