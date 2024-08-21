<?php

namespace Tools\Phpat;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class LayerTest
{
    private const DOMAIN_NAMESPACE = 'DaveLiddament\StaticAnalysisResultsBaseliner\Domain';
    private const PLUGIN_NAMESPACE = 'DaveLiddament\StaticAnalysisResultsBaseliner\Plugins';
    private const LEGACY_NAMESPACE = 'DaveLiddament\StaticAnalysisResultsBaseliner\Legacy';
    private const FRAMEWORK_NAMESPACE = 'DaveLiddament\StaticAnalysisResultsBaseliner\Framework';

    public function testDomainDoesNotDependOnOtherLayers(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::DOMAIN_NAMESPACE))
            ->shouldNotDependOn()
            ->classes(
                Selector::inNamespace(self::PLUGIN_NAMESPACE),
                Selector::inNamespace(self::LEGACY_NAMESPACE),
                Selector::inNamespace(self::FRAMEWORK_NAMESPACE),
            )->because('Domain code should not depend on any other code')
        ;
    }

    public function testsPluginDoesNotDependOnFrameworkLayer(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::PLUGIN_NAMESPACE))
            ->shouldNotDependOn()
            ->classes(
                Selector::inNamespace(self::FRAMEWORK_NAMESPACE),
                Selector::inNamespace(self::LEGACY_NAMESPACE),
            )->because('Plugin code should not depend on framework or legacy code')
        ;
    }

    public function testsLegacyDoesNotDependOnFrameworkLayer(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::LEGACY_NAMESPACE))
            ->shouldNotDependOn()
            ->classes(
                Selector::inNamespace(self::FRAMEWORK_NAMESPACE),
            )->because('Legacy code should not depend on framework code')
        ;
    }
}
