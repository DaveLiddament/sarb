<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser;

/**
 * Looks up the HistoryFactory of the given name.
 */
interface HistoryFactoryLookupService
{
    /**
     * Return HistoryFactory of the given name.
     *
     * @param string $identifier
     *
     * @throws InvalidHistoryFactoryException
     *
     * @return HistoryFactory
     */
    public function getHistoryFactory(string $identifier): HistoryFactory;
}
