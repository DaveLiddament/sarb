<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

/**
 * Thrown when the baseline was created from results containing type identifiers provided by the
 * static analysis tool, but the supplied results contain none. None of the baseline results could
 * be matched, so every baselined issue would be reported as new.
 */
final class InputMissingTypeIdentifiersException extends SarbException
{
    public static function baseLineBuiltFromTypeIdentifiers(): self
    {
        return new self(
            'The baseline was created from results that contained type identifiers, '.
            'but the supplied results contain none. '.
            'This usually means the baseline was created with a newer version of the static analysis tool '.
            '(e.g. PHPStan >= 1.11) than the one that produced these results. '.
            'Either use the version of the tool the baseline was created with, or regenerate the baseline.',
        );
    }
}
