<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter;

interface OutputFormatterLookupService
{
    /**
     * Returns OutputFormatter of the given name.
     *
     * @throws InvalidOutputFormatterException
     */
    public function getOutputFormatter(string $identifier): OutputFormatter;

    /**
     * Returns a list of all OutputFormatter identifiers.
     *
     * These are used to identify which OutputFormatter to use.
     *
     * @return string[]
     */
    public function getIdentifiers(): array;
}
