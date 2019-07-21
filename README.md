# MonkeyPatch
Monkey patch for php.

## Usage
```php
(new \MonkeyPatch\Patcher())->whenLoad('/path/to/src')->patchBy(new class extends AbstractCodeFilter {
    public function transformCode(string $code): string {
        // fix $code by your logic.
        return $code;
    }
});
```
