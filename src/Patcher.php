<?php
namespace MonkeyPatch;

use MonkeyPatch\Filters\AbstractAstFilter;
use MonkeyPatch\Filters\AbstractCodeFilter;
use MonkeyPatch\Processors\Configuration;
use MonkeyPatch\Processors\StreamProcessor;
use PhpParser\NodeVisitorAbstract;

class Patcher
{
    private $processor;

    public function __construct(Configuration $configuration = null)
    {
        $this->processor = new StreamProcessor($configuration ?? new Configuration());
    }

    public function whenLoad(string $fileOrDir): self
    {
        $this->processor->getConfiguration()->addWhiteList($fileOrDir);

        return $this;
    }

    public function ignore(string $fileOrDir): self
    {
        $this->processor->getConfiguration()->addBlackList($fileOrDir);

        return $this;
    }

    public function patchBy(AbstractCodeFilter $filter): void
    {
        $filter->register();
        $this->processor->appendFilter($filter);

        $this->processor->intercept();
    }
}
