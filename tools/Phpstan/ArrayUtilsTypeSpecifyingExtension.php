<?php

declare(strict_types=1);

namespace Tools\Phpstan;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;

class ArrayUtilsTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;

    public function getClass(): string
    {
        return ArrayUtils::class;
    }

    public function isStaticMethodSupported(
        MethodReflection $staticMethodReflection,
        StaticCall $node,
        TypeSpecifierContext $context,
    ): bool {
        return 'assertArray' === $staticMethodReflection->getName()
            && isset($node->getArgs()[0])
            && $context->null();
    }

    public function specifyTypes(
        MethodReflection $staticMethodReflection,
        StaticCall $node,
        Scope $scope,
        TypeSpecifierContext $context,
    ): SpecifiedTypes {
        return $this->typeSpecifier->create(
            $node->getArgs()[0]->value,
            new ArrayType(new MixedType(), new MixedType()),
            TypeSpecifierContext::createTruthy(),
        );
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
