# MonkeyPatch
Monkey patch for php. Transform php code when autoload. (php file itself will be not changed)

## Example
When include SomeClass (as below) by autoload or include/require.
```php
<?php
namespace SomeNamespace;

class SomeClass
{
    public function someMethod()
    {
        $client = new \GuzzleHttp\Client();
        return $client->request('GET', 'https://your.production.env.com/api/end_point');
    }
}
```

You can fix php code.

```php
(new \MonkeyPatch\Patcher())->whenLoad('/path/to/src')->patchBy(new class extends \MonkeyPatch\Filters\AbstractCodeFilter {
    public function transformCode(string $code): string
    {
        // fix url.
        // 'https://your.production.env.com' -> 'https://your.test.env.com'
        return preg_replace('/https:\/\/your\.production\.env\.com/', 'https://your.test.env.com', $code);
    }
});
```

In this case, SomeClass will be included as below. (Of course, original php file will be not changed.)

```php
<?php
namespace SomeNamespace;

class SomeClass
{
    public function someMethod()
    {
        $client = new \GuzzleHttp\Client();
        return $client->request('GET', 'https://your.test.env.com/api/end_point');
    }
}
```

## Usage
```php
(new \MonkeyPatch\Patcher())->whenLoad('/path/to/src')->patchBy(new class extends \MonkeyPatch\Filters\AbstractCodeFilter {
    public function transformCode(string $code): string
    {
        // fix $code by your logic.
        return $code;
    }
});
```

If you want to use php-parser, use AbstractAstFilter.
```php
(new \MonkeyPatch\Patcher())->whenLoad('/path/to/src')->patchBy(new class extends \MonkeyPatch\Filters\AbstractAstFilter {
    protected function getVisitor(): NodeVisitorAbstract
    {
        // fix AST by your visitor.
        return new class extends NodeVisitorAbstract {
            //...
        }
    }
});
```
