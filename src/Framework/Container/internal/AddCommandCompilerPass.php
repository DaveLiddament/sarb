<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\internal;

use Composer\InstalledVersions;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddCommandCompilerPass implements CompilerPassInterface
{
    private const PACKAGE_NAME = 'dave-liddament/sarb';

    public function process(ContainerBuilder $container): void
    {
        $container->register(Application::class, Application::class);
        $definition = $container->getDefinition(Application::class);
        $definition->setArguments(['SARB', $this->getVersion()]);
        $definition->setPublic(true);
        $taggedServices = $container->findTaggedServiceIds(Container::COMMAND_TAG);
        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall('add', [new Reference($id)]);
        }
    }

    private function getVersion(): string
    {
        return InstalledVersions::isInstalled(self::PACKAGE_NAME)
            ? (InstalledVersions::getPrettyVersion(self::PACKAGE_NAME) ?? 'UNKNOWN')
            : 'UNKNOWN';
    }
}
