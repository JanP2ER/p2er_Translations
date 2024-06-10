<?php

namespace P2er\Cache;

class CacheFile
{
    /**
     * @var string
     */
    private string $dir = '';

    /**
     * @var string
     */
    private string $typ = '';

    /**
     * @var int
     */
    private int $defaultTtl = 0;

    /**
     * @var bool
     */
    private bool $skipGc = false;

    /**
     * @var bool
     */
    public bool $hashedKey = true;

    /**
     * @param string $dir
     * @param string $typ
     * @param int $defaultTtl in seconds
     * @param bool $skipGc
     * @param bool $dumpGc // Delete everything instead of just invalidated
     * @param bool $autoCreate
     */
    public function __construct(
        string $dir = '/tmp',
        string $typ = '',
        int $defaultTtl = 0,
        bool $dumpGc = false,
        bool $skipGc = false,
        bool $autoCreate = true
    ) {
        $this->dir = $dir;
        $this->typ = $typ;
        $this->defaultTtl = $defaultTtl;
        $this->skipGc = $skipGc;
        if (!file_exists($this->dir)) {
            if ($autoCreate) {
                mkdir($this->dir, 0777, true);
            } else {
                user_error('File Cache: Directory ' . $this->dir . ' does not exist');
            }
        }

        // Garbage collect once a day
        if (!$this->avail('garbageCollection')) {
            $dumpGc ? $this->dump() : $this->gc();
            $this->set('garbageCollection', ['updated' => time()], 86400);
        }
    }

    /**
     * Remove depricated items from cache on every 100th deconstruct
     */
    public function __destruct()
    {
        if (!$this->skipGc && mt_rand(0, 100) === 0) {
            $this->gc();
        }
    }

    /**
     * @param string $key
     * @param bool $expired
     * @return bool
     */
    public function avail(string $key, bool $expired = false): bool
    {
        $filename = $this->getFileName($key);
        if (!file_exists($filename)) {
            return false;
        }
        if (!$expired && time() > filemtime($filename)) {
            return false;
        }

        return true;
    }

    /**
     * Remove all files from cache
     * @return array
     */
    public function dump(): array
    {
        $results = [];
        $filePattern = $this->dir . '/' . preg_replace('/[^a-z0-9]/iu', '', $this->typ) . '-[a-fA-F0-9]*.cache';
        foreach (glob($filePattern) as $filename) {
            if (!file_exists($filename)) {
                continue;
            }
            $content = file_get_contents($filename);
            if (!mb_strlen($content)) {
                continue;
            }
            $data = unserialize(gzuncompress($content));
            $results[] = $data;
        }

        return $results;
    }

    /**
     * Get cached content by key
     *
     * @param mixed $key
     * @param bool $expired Flag if expired content should be returned
     * @return mixed|null content or NULL if key is not found
     */
    public function get(string $key, bool $expired = false)
    {
        $filename = $this->getFileName($key);
        if (!file_exists($filename)) {
            return null;
        }
        if (!$expired && time() > filemtime($filename)) {
            return null;
        }
        $content = file_get_contents($filename);
        if (!mb_strlen($content)) {
            return null;
        }

        $uncompressed = gzuncompress($content);
        if (!$uncompressed) {
            return null;
        }
        return unserialize($uncompressed);
    }

    /**
     * Put content in cache
     *
     * @param mixed $key Key to identify the value
     * @param mixed $value Content to by cached by $key
     * @param int $ttl [optional] Time in seconds after that the content should be expired
     * @return bool success
     */
    public function set(string $key, $value, int $ttl = 0): bool
    {
        $filename = $this->getFileName($key);
        $ret = file_put_contents($filename, gzcompress(serialize($value)));
        touch($filename, time() + ($ttl !== 0 ? $ttl : $this->defaultTtl));

        return ($ret !== false);
    }

    /**
     * Remove content from cache
     *
     * @param string $key
     */
    public function del(string $key): void
    {
        $fileName = $this->getFileName($key);
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * Garbage collector - Removed expired entries from cache
     */
    public function gc(): void
    {
        $this->purge(false);
    }

    /**
     * @param bool $gcAll
     */
    private function purge(bool $gcAll): void
    {
        $dir = dirname($this->getFileName(''));
        $handle = opendir($dir);
        if (!$handle) {
            return;
        }

        $now = time();
        $matchRe = '/^' . preg_replace('/[^a-z0-9]/iu', '', $this->typ) . '-\w+\.cache$/';
        while (($file = readdir($handle)) !== false) {
            if (!preg_match($matchRe . 'u', $file)) {
                continue;
            }
            $path = $dir . '/' . $file;
            $isFile = is_file($path);
            if (!$isFile) {
                continue;
            }
            if (!$gcAll && $now <= filemtime($path)) {
                continue;
            }
            $fileExists = file_exists($path);
            if ($fileExists) {
                @unlink($path);
            }
        }
    }

    public function purgeAll(): void
    {
        $this->purge(true);
    }

    /**
     * Get Cache-Filename for a mixed key variable
     *
     * @param string $key
     * @return string(32)
     */
    protected function getFileName(string $key): string
    {
        $keyName = $this->hashedKey ? md5(serialize($key)) : $key;
        $filename = $this->dir
            . '/'
            . preg_replace('/[^a-z0-9]/iu', '', $this->typ)
            . '-'
            . $keyName
            . '.cache';

        $dir = dirname($filename);
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $filename;
    }
}
