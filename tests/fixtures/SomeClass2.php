<?php
declare(strict_types=1);

namespace MonkeyPatch\Tests\Fixtures;

use MonkeyPatch\Tests\Fixtures\SomeClass1;

const SOME_CONSTANT2 = 'some constant2';

$sideEffect = 1;

sideEffect(1, 2, 3);

class SomeClass2
{
    private $someProperty1;
    protected $someProperty2;
    public $someProperty3;

    private function someMethod1()
    {
        return $this->someProperty1;
    }

    protected function someMethod2()
    {
        return $this->someMethod1();
    }

    public function someMethod3()
    {
        return $this->someProperty3;
    }
}
