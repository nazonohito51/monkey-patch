<?php
declare(strict_types=1);

namespace Tests\Unit\MonkeyPatch;

use MonkeyPatch\Filters\AbstractCodeFilter;
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
        (new Patcher($configuration))->whenLoad($this->getFixturePath('SomeClass3.php'))->patchBy(new class extends AbstractCodeFilter {
            protected function transformCode(string $code): string
            {
                return preg_replace('/https:\/\/your\.production\.env\.com/', 'https://your.test.env.com', $code);
            }
        });

        $expected = <<<CODE
<?php
declare(strict_types=1);

namespace MonkeyPatch\Tests\Fixtures;

class SomeClass3
{
    public function someMethod()
    {
        \$client = new \GuzzleHttp\Client();
        return \$client->request('GET', 'https://your.test.env.com/api/end_point');
    }
}

CODE;
        $this->assertSame($expected, file_get_contents($this->getFixturePath('SomeClass3.php')));
    }
}
