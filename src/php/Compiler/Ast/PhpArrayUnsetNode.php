<?php

declare(strict_types=1);

namespace Phel\Compiler\Ast;

use Phel\Compiler\Environment\NodeEnvironmentInterface;
use Phel\Lang\SourceLocation;

final class PhpArrayUnsetNode extends AbstractNode
{
    private AbstractNode $arrayExpr;
    private AbstractNode $accessExpr;

    public function __construct(
        NodeEnvironmentInterface $env,
        AbstractNode $arrayExpr,
        AbstractNode $accessExpr,
        ?SourceLocation $sourceLocation = null
    ) {
        parent::__construct($env, $sourceLocation);
        $this->arrayExpr = $arrayExpr;
        $this->accessExpr = $accessExpr;
    }

    public function getArrayExpr(): AbstractNode
    {
        return $this->arrayExpr;
    }

    public function getAccessExpr(): AbstractNode
    {
        return $this->accessExpr;
    }
}
