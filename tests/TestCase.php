<?php
declare(strict_types=1);

namespace MonkeyPatch\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getFixturePath(string $file)
    {
        $base = __DIR__ . '/fixtures';
        $file = substr($file, 0, 1) === '/' ? $file : "/{$file}";

        return $base . $file;
    }
}
