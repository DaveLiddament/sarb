<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser;

/**
 * Looks up the HistoryFactory of the given name.
 */
interface HistoryFactoryLookupService
{
    /**
     * Return HistoryFactory of the given name.
     *
     * @throws InvalidHistoryFactoryException
     */
    public function getHistoryFactory(string $identifier): HistoryFactory;

    /**
     * Returns a list of all HistoryFactory identifiers.
     *
     * These are used to identify which HistoryFactory to use.
     *
     * @return string[]
     */
    public function getIdentifiers(): array;
}
