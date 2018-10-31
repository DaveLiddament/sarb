<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Framework\Container\internal;

use DaveLiddament\StaticAnalysisBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisBaseliner\Framework\Container\HistoryFactoryRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddHistoryFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(HistoryFactoryRegistry::class);
        $taggedServices = $container->findTaggedServiceIds(Container::HISTORY_FACTORY_TAG);
        $services = [];
        foreach ($taggedServices as $id => $tags) {
            $services[] = new Reference($id);
        }
        $definition->setArgument(0, $services);
    }
}
