<?php

namespace MonkeyPatch\Processors;

use MonkeyPatch\Filters\AbstractCodeFilter;

class StreamProcessor
{
    const STREAM_OPEN_FOR_INCLUDE = 128;
    const PROTOCOL = 'file';

    /**
     * @var Configuration
     */
    protected static $configuration;

    /**
     * @var AbstractCodeFilter[]
     */
    protected static $filters = [];

    /**
     * @var resource
     */
    protected $resource;

    public $context;
    protected $isIntercepting = false;

    public function __construct(Configuration $configuration = null)
    {
        if ($configuration) {
            static::$configuration = $configuration;
        }
    }

    public function getConfiguration(): Configuration
    {
        return static::$configuration;
    }

    public function appendFilter(AbstractCodeFilter $filter): void
    {
        static::$filters[$filter->getFilterName()] = $filter;
    }

    /**
     * @param resource $stream
     */
    protected function appendFiltersToStream($stream)
    {
        foreach (static::$filters as $filter) {
            stream_filter_append($stream, $filter->getFilterName(), STREAM_FILTER_READ);
        }
    }

    public function intercept(): void
    {
        if (!$this->isIntercepting) {
            stream_wrapper_unregister(self::PROTOCOL);
            $this->isIntercepting = stream_wrapper_register(self::PROTOCOL, __CLASS__);
        }
    }

    public function restore(): void
    {
        stream_wrapper_restore(self::PROTOCOL);
    }

    /**
     * @see https://www.php.net/manual/en/streamwrapper.stream-open.php
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$openedPath)
    {
        if ('r' === substr($mode, 0, 1) && !file_exists($path)) {
            return false;
        }

        $this->restore();

        if (isset($this->context)) {
            $this->resource = fopen($path, $mode, $options & STREAM_USE_PATH, $this->context);
        } else {
            $this->resource = fopen($path, $mode, $options & STREAM_USE_PATH);
        }

        if (($options & self::STREAM_OPEN_FOR_INCLUDE || self::$configuration->canFilterReadFileContent()) &&
            self::$configuration->shouldProcess($path)) {
            $this->appendFiltersToStream($this->resource);
        }

        $this->intercept();

        return $this->resource !== false;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-close.php
     * @return bool
     */
    public function stream_close()
    {
        return fclose($this->resource);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-eof.php
     * @return bool
     */
    public function stream_eof()
    {
        return feof($this->resource);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-flush.php
     * @return bool
     */
    public function stream_flush()
    {
        return fflush($this->resource);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-read.php
     * @param int $count
     * @return string
     */
    public function stream_read($count)
    {
        return fread($this->resource, $count);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-seek.php
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->resource, $offset, $whence) === 0;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-stat.php
     * @return array
     */
    public function stream_stat()
    {
        return fstat($this->resource);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-tell.php
     * @return int
     */
    public function stream_tell()
    {
        return ftell($this->resource);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.url-stat.php
     * @param string $path
     * @param int $flags
     * @return int
     */
    public function url_stat($path, $flags)
    {
        $this->restore();
        if ($flags & STREAM_URL_STAT_QUIET) {
            set_error_handler(function () {
                // Use native error handler
                return false;
            });
            $result = @stat($path);
            restore_error_handler();
        } else {
            $result = stat($path);
        }
        $this->intercept();

        return $result;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.dir-closedir.php
     * @return bool
     */
    public function dir_closedir()
    {
        closedir($this->resource);

        return true;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.dir-opendir.php
     * @param string $path
     * @return bool
     */
    public function dir_opendir($path)
    {
        $this->restore();
        if (isset($this->context)) {
            $this->resource = opendir($path, $this->context);
        } else {
            $this->resource = opendir($path);
        }
        $this->intercept();

        return $this->resource !== false;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.dir-readdir.php
     * @return mixed
     */
    public function dir_readdir()
    {
        return readdir($this->resource);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.dir-rewinddir.php
     * @return bool
     */
    public function dir_rewinddir()
    {
        rewinddir($this->resource);

        return true;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.mkdir.php
     * @param string $path
     * @param int $mode
     * @param integer $options
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        $this->restore();
        if (isset($this->context)) {
            $result = mkdir($path, $mode, $options, $this->context);
        } else {
            $result = mkdir($path, $mode, $options);
        }
        $this->intercept();

        return $result;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.rename.php
     * @param string $path_from
     * @param string $path_to
     * @return bool
     */
    public function rename($path_from, $path_to)
    {
        $this->restore();
        if (isset($this->context)) {
            $result = rename($path_from, $path_to, $this->context);
        } else {
            $result = rename($path_from, $path_to);
        }
        $this->intercept();

        return $result;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.rmdir.php
     * @param string $path
     * @return bool
     */
    public function rmdir($path)
    {
        $this->restore();
        if (isset($this->context)) {
            $result = rmdir($path, $this->context);
        } else {
            $result = rmdir($path);
        }
        $this->intercept();

        return $result;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-cast.php
     * @param int $cast_as
     * @return resource
     */
    public function stream_cast($cast_as)
    {
        return $this->resource;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-lock.php
     * @param int $operation
     * @return bool
     */
    public function stream_lock($operation)
    {
        $operation = ($operation === 0 ? LOCK_EX : $operation);
        return flock($this->resource, $operation);
    }

    /**
     * @see https://www.php.net/manual/en/streamwrapper.stream-set-option.php
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->resource, $arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->resource, $arg1, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                return stream_set_write_buffer($this->resource, $arg1);
            case STREAM_OPTION_READ_BUFFER:
                return stream_set_read_buffer($this->resource, $arg1);
            case STREAM_OPTION_CHUNK_SIZE:
                return stream_set_chunk_size($this->resource, $arg1);
        }
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-write.php
     * @param string $data
     * @return int
     */
    public function stream_write($data)
    {
        return fwrite($this->resource, $data);
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.unlink.php
     * @param string $path
     * @return bool
     */
    public function unlink($path)
    {
        $this->restore();
        if (isset($this->context)) {
            $result = unlink($path, $this->context);
        } else {
            $result = unlink($path);
        }
        $this->intercept();

        return $result;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-metadata.php
     * @param string $path
     * @param integer $option
     * @param mixed $value
     * @return bool
     */
    public function stream_metadata($path, $option, $value)
    {
        $this->restore();
        $result = null;

        switch ($option) {
            case STREAM_META_TOUCH:
                if (empty($value)) {
                    $result = touch($path);
                } else {
                    $result = touch($path, $value[0], $value[1]);
                }
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                $result = chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                $result = chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                $result = chmod($path, $value);
                break;
        }
        $this->intercept();

        return $result;
    }

    /**
     * @see http://www.php.net/manual/en/streamwrapper.stream-truncate.php
     * @param int $new_size
     * @return bool
     */
    public function stream_truncate($new_size)
    {
        return ftruncate($this->resource, $new_size);
    }
}
