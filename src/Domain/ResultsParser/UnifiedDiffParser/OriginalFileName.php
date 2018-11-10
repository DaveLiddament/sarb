<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;

/**
 * Wrapper class to reduce the chance of mixing Original and New FileNames.
 */
class OriginalFileName extends FileName
{
}
