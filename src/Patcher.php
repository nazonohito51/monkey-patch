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

    public function whenLoad(string $path): self
    {
        $this->processor->getConfiguration()->addWhiteList($path);

        return $this;
    }

    /**
     * @param AbstractCodeFilter $filter
     */
    public function patchBy(AbstractCodeFilter $filter): void
    {
        $filter->register();
        $this->processor->appendFilter($filter);

        $this->processor->intercept();
    }
}
