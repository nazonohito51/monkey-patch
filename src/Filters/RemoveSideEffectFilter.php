<?php
declare(strict_types=1);

namespace MonkeyPatch\Filters;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class RemoveSideEffectFilter extends AbstractAstFilter
{
    public function getFilterName(): string
    {
        return 'remove_side_effect_filter';
    }

    protected function getVisitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract {
            private $isInSymbol = false;
            private $isInConditionalStatement = false;
            private $namespaceNameNodes = [];

            public function leaveNode(Node $node) {
                if (!$this->isInSymbol && !$this->isInConditionalStatement && !in_array($node, $this->namespaceNameNodes)) {
                    if (!$this->verifyNamespaceNode($node) && !$this->verifyConditionalNode($node) && !$this->verifySymbolNode($node)) {
                        if ($node instanceof Stmt) {
                            return NodeTraverser::REMOVE_NODE;
                        }
                    }
                }

                if ($this->verifySymbolNode($node)) {
                    $this->isInSymbol = false;
                } elseif ($this->verifyConditionalNode($node)) {
                    $this->isInConditionalStatement = false;
                }

                return null;
            }

            public function enterNode(Node $node) {
                if ($this->verifySymbolNode($node)) {
                    $this->isInSymbol = true;
                } elseif ($this->verifyConditionalNode($node)) {
                    $this->isInConditionalStatement = true;
                } elseif ($this->verifyNamespaceNode($node)) {
                    $this->namespaceNameNodes[] = $node->name;
                }
            }

            private function verifySymbolNode(Node $node): bool
            {
                return $node instanceof Stmt\ClassLike || $node instanceof Stmt\Function_;
            }

            private function verifyConditionalNode(Node $node): bool
            {
                return $node instanceof Stmt\Use_ || $node instanceof Stmt\Declare_ || $node instanceof Stmt\Const_;
            }

            private function verifyNamespaceNode(Node $node): bool
            {
                return $node instanceof Stmt\Namespace_;
            }
        };
    }
}
