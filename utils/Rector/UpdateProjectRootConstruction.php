<?php

declare(strict_types=1);


namespace Utils\Rector;


use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

class UpdateProjectRootConstruction extends AbstractRector
{

    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            "Updates call to `new ProjectRoot`",
            [
                new CodeSample(
                    'new ProjectRoot',
                    'ProjectRoot::fromProjectRoot'
                ),
            ]
        );
    }

    /** @param New_ $node */
    public function refactor(Node $node): ?Node
    {
        Assert::isInstanceOf($node, New_::class);

        if (!$node->class ===  null) {
            return null;
        }


        if (!$node->class instanceof Name) {
            return null;
        }


        if ($node->class->parts !== ['DaveLiddament', 'StaticAnalysisResultsBaseliner', 'Domain', 'Common', 'ProjectRoot'])
        {
            return null;
        }

        $args = $node->args;

        if (
            ($args[0]->value instanceof Node\Scalar\String_) &&
            ($args[1]->value instanceof Node\Scalar\String_) &&
            ($args[0]->value->value === $args[1]->value->value)
            ) {

            return new StaticCall(new Name\FullyQualified(ProjectRoot::class), 'fromCurrentWorkingDirectory', [$args[0]]);
        }



        if (
            ($args[0]->value instanceof Node\Expr\ClassConstFetch) &&
            ($args[1]->value instanceof Node\Expr\ClassConstFetch) &&
            ($args[0]->value->name instanceof Node\Identifier) &&
            ($args[1]->value->name instanceof Node\Identifier) &&
            ($args[0]->value->name->name === $args[1]->value->name->name)

            ) {

            return new StaticCall(new Name\FullyQualified(ProjectRoot::class), 'fromCurrentWorkingDirectory', [$args[0]]);
        }



        return new StaticCall(new Name\FullyQualified(ProjectRoot::class), 'fromProjectRoot', $args);

    }
}
