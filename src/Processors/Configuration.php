<?php

namespace MonkeyPatch\Processors;

class Configuration
{
    private $whiteList = [];
    private $blackList = [];
    private $canFilterReadFileContent = false;

    public function addWhiteList(string $fileOrDir): void
    {
        if (!file_exists($fileOrDir)) {
            throw new \InvalidArgumentException('path is not exist: ' . $fileOrDir);
        }

        $this->whiteList[] = $fileOrDir;
    }

    public function addBlackList(string $fileOrDir): void
    {
        if (!file_exists($fileOrDir)) {
            throw new \InvalidArgumentException('path is not exist: ' . $fileOrDir);
        }

        $this->blackList[] = $fileOrDir;
    }

    public function shouldProcess(string $uri): bool
    {
        return $this->isPhpFile($uri) && $this->isWhitelisted($uri) && !$this->isBlacklisted($uri);
    }

    private function isPhpFile(string $uri): bool
    {
        return pathinfo($uri, PATHINFO_EXTENSION) === 'php';
    }

    private function isWhitelisted(string $uri): bool
    {
        if (empty($this->whiteList)) {
            return true;
        }

        foreach ($this->whiteList as $path) {
            if (strpos($uri, $path) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isBlacklisted(string $uri): bool
    {
        foreach ($this->blackList as $path) {
            if (strpos($uri, $path) !== false) {
                return true;
            }
        }

        return false;
    }

    public function enableFilterReadFileContent()
    {
        $this->canFilterReadFileContent = true;
    }

    public function canFilterReadFileContent()
    {
        return $this->canFilterReadFileContent;
    }
}
