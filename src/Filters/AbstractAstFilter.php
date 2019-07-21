<?php
declare(strict_types=1);

namespace MonkeyPatch\Filters;

use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

abstract class AbstractAstFilter extends AbstractCodeFilter
{
    abstract protected function getVisitor(): NodeVisitorAbstract;

    protected function transformCode(string $code): string
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            // TODO: error handling
            throw $error;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->getVisitor());
        $newAst = $traverser->traverse($ast);

        return (new Standard)->prettyPrintFile($newAst);
    }
}
