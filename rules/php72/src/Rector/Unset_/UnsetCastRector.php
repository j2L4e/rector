<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\Unset_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Unset_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Php72\Tests\Rector\Unset_\UnsetCastRector\UnsetCastRectorTest
 */
final class UnsetCastRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes (unset) cast', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$different = (unset) $value;

$value = (unset) $value;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$different = null;

unset($value);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Unset_::class, Assign::class];
    }

    /**
     * @param Unset_|Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            if ($node->expr instanceof Unset_) {
                $unset = $node->expr;

                if ($this->areNodesEqual($node->var, $unset->expr)) {
                    return $this->createFuncCall('unset', [$node->var]);
                }
            }

            return null;
        }
        $nodeNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        if ($nodeNode instanceof Expression) {
            $this->removeNode($node);

            return null;
        }

        return $this->createNull();
    }
}
