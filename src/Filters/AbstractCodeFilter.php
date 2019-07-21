<?php
declare(strict_types=1);

namespace MonkeyPatch\Filters;

abstract class AbstractCodeFilter extends \PHP_User_Filter
{
    abstract public function getFilterName(): string;
    abstract protected function transformCode(string $code): string;

    protected $isRegistered = false;

    public function register(): bool
    {
        if (!$this->isRegistered) {
            $this->isRegistered = stream_filter_register($this->getFilterName(), get_called_class());
        }

        return $this->isRegistered;
    }

    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = $this->transformCode($bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
