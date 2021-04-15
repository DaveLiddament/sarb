<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\OutputFormatterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddOutputFormatterFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(OutputFormatterRegistry::class);
        $taggedServices = $container->findTaggedServiceIds(Container::OUTPUT_FORMATTER_TAG);
        $services = [];
        foreach (array_keys($taggedServices) as $id) {
            $services[] = new Reference($id);
        }
        $definition->setArgument(0, $services);
    }
}
