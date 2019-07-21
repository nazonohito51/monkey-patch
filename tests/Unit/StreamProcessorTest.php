<?php
declare(strict_types=1);

namespace Tests\Unit\MonkeyPatch;

use MonkeyPatch\Filters\RemoveSideEffectFilter;
use MonkeyPatch\Processors\Configuration;
use MonkeyPatch\Processors\StreamProcessor;
use MonkeyPatch\Tests\TestCase;

class StreamProcessorTest extends TestCase
{
    /**
     * @var StreamProcessor
     */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();
        $config = new Configuration();
        $config->addWhiteList('/Users/kawashimatoshi/git/nazonohito51/monkey-patch/tests');
        $config->enableFilterReadFileContent();
        $this->sut = new StreamProcessor($config);
    }

    public function tearDown(): void
    {
        $this->sut->restore();
        parent::tearDown();
    }

    public function provideIntercept()
    {
        $expectedSomeClass1 = <<<CODE
<?php

declare (strict_types=1);
namespace MonkeyPatch\Tests\Fixtures;

use MonkeyPatch\Tests\Fixtures\SomeClass2;
const SOME_CONSTANT1 = 'some constant1';
class SomeClass1
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

        $expectedSomeClass2 = <<<CODE
<?php

declare (strict_types=1);
namespace MonkeyPatch\Tests\Fixtures;

use MonkeyPatch\Tests\Fixtures\SomeClass1;
const SOME_CONSTANT2 = 'some constant2';
class SomeClass2
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

        return [
            [$this->getFixturePath('/SomeClass1.php'), $expectedSomeClass1],
            [$this->getFixturePath('/SomeClass2.php'), $expectedSomeClass2],
        ];
    }

    /**
     * @param string $path
     * @param string $expected
     * @dataProvider provideIntercept
     */
    public function testIntercept(string $path, string $expected)
    {
        $filter = new RemoveSideEffectFilter();
        $filter->register();
        $this->sut->appendFilter($filter);
        $this->sut->intercept();

//        $this->assertSame($expected, include $path);
        $this->assertSame($expected, file_get_contents($path));
    }
}
