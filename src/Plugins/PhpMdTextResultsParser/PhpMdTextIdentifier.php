<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpMdTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhpMdTextIdentifier implements Identifier
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'phpmd-text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'PHP Mess Detector. To generate use: phpmd <code_directory> text <phpmd.xml location> --reportfile <output.txt>';
    }
}
