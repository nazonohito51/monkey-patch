<?php
declare(strict_types=1);

namespace Tests\Unit\MonkeyPatch;

use MonkeyPatch\Filters\RemoveSideEffectFilter;
use MonkeyPatch\Patcher;
use MonkeyPatch\Processors\Configuration;
use MonkeyPatch\Processors\StreamProcessor;
use MonkeyPatch\Tests\TestCase;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class PatcherTest extends TestCase
{
    public function testPatch()
    {
        $configuration = new Configuration();
        $configuration->enableFilterReadFileContent();
        (new Patcher($configuration))->whenLoad($this->getFixturePath('SomeClass3.php'))->patchBy(new RemoveSideEffectFilter());

        $expected = <<<CODE
<?php

declare (strict_types=1);
namespace MonkeyPatch\Tests\Fixtures;

use MonkeyPatch\Tests\Fixtures\SomeClass1;
const SOME_CONSTANT3 = 'some constant3';
class SomeClass3
{
    private \$someProperty1;
    protected \$someProperty2;
    public \$someProperty3;
    private function someMethod1()
    {
        return \$this->someProperty1;
    }
    protected function someMethod2()
    {
        return \$this->someMethod1();
    }
    public function someMethod3()
    {
        return \$this->someProperty3;
    }
}
CODE;
        $this->assertSame($expected, file_get_contents($this->getFixturePath('SomeClass3.php')));
    }
}
