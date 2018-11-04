<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryFactory;
use Webmozart\Assert\Assert;

class HistoryFactoryRegistry
{
    /**
     * @var HistoryFactory[]
     * @psalm-var array<string, HistoryFactory>
     */
    private $historyFactories;

    /**
     * HistoryFactoryRegistry constructor.
     *
     * @param HistoryFactory[] $historyFactories
     */
    public function __construct(array $historyFactories)
    {
        $this->historyFactories = [];
        foreach ($historyFactories as $historyFactory) {
            $this->addHistoryFactory($historyFactory);
        }
    }

    /**
     * Returns a list of all HistoryFactory identifiers.
     *
     * These are used to identify which HistoryFactory to use.
     *
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return array_keys($this->historyFactories);
    }

    public function getHistoryFactory(string $identifier): HistoryFactory
    {
        Assert::keyExists($this->historyFactories, $identifier);

        return $this->historyFactories[$identifier];
    }

    private function addHistoryFactory(HistoryFactory $historyFactory): void
    {
        $identifier = $historyFactory->getIdentifier();
        Assert::keyNotExists($this->historyFactories, $identifier,
            "Multiple History Factories configured with the identifier [$identifier]"
        );

        $this->historyFactories[$identifier] = $historyFactory;
    }
}
