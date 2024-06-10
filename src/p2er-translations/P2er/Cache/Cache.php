<?php

namespace P2er\Cache;

require_once(__DIR__ . '/CacheState.php');
require_once(__DIR__ . '/CacheFile.php');

class Cache
{
    public static $TYPE_FILE = 'file';
    public static $TYPE_STATE = 'state';

    /**
     * @var CacheState
     */
    public CacheState $state;

    /**
     * @var bool
     */
    public bool $nocache = false;

    /**
     * @var float
     */
    public float $ttl = 86400.0;

    /**
     * @var CacheFile
     */
    public CacheFile $fileCache;

    /**
     * Prefix cache entries
     * @var string
     */
    public string $prefix = '';

    /**
     * Cache directory
     * @var string
     */
    public string $dir = '/tmp';

    /**
     * Cache type
     * @var string
     */
    public string $typ = 'p2er';

    /**
     * @param bool $nocache
     */
    public function __construct(bool $nocache = false)
    {
        $this->nocache = $nocache;
        $this->prefix = '';
        $this->ttl = 86400.0;
    }

    /**
     * @return CacheFile
     */
    public function getFileCache(): CacheFile
    {
        if (!isset($this->fileCache)) {
            // Separate country caches
            $dir = $this->dir;
            if ($this->prefix !== '') {
                $dir .= '/' . $this->prefix;
            }

            // Create file cache in tmp or subfolder subfolder
            $this->fileCache = new CacheFile($dir, $this->typ, $this->ttl, false);

            // Clear initially if nocache is set
            if ($this->nocache) {
                $this->fileCache->purgeAll();
            }
        }
        return $this->fileCache;
    }

    /**
     * @return CacheState
     */
    public function getState(): CacheState
    {
        if (!isset($this->statee)) {
            $this->state = new CacheState();
        }
        return $this->state;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getValidType(string $type): string
    {
        switch ($type) {
            case self::$TYPE_FILE:
            case self::$TYPE_STATE:
                return $type;
            default:
                return self::$TYPE_FILE;
        }
    }

    /**
     * Fetch data from cache
     * @param string $cache_id
     * @param string $type
     * @return mixed|null
     */
    public function get(string $cache_id, string $type = 'file')
    {
        $cache_id = $this->prefix . $cache_id;
        $type = $this->getValidType($type);
        switch ($type) {
            case self::$TYPE_STATE:
                return $this->getFromState($cache_id);
            case self::$TYPE_FILE:
            default:
                return $this->getFromFile($cache_id);
        }
    }

    /**
     * Place data into cache
     * @param string $cache_id
     * @param $data
     * @param string $type
     * @param int $ttl
     * @return bool
     */
    public function put(string $cache_id, $data, string $type = 'file', int $ttl = 0): bool
    {
        $cache_id = $this->prefix . $cache_id;
        $type = $this->getValidType($type);
        if ($ttl <= 0) {
            $ttl = $this->ttl;
        }
        switch ($type) {
            case self::$TYPE_STATE:
                return $this->putToState($cache_id, $data);
            case self::$TYPE_FILE:
            default:
                return $this->putToFile($cache_id, $data, $ttl);
        }
    }

    /**
     * Place data into cache
     * @param string $cache_id
     * @param string $type
     * @return bool
     */
    public function del(string $cache_id, string $type = 'file'): bool
    {
        $cache_id = $this->prefix . $cache_id;
        $type = $this->getValidType($type);
        switch ($type) {
            case self::$TYPE_STATE:
                return $this->removeFromState($cache_id);
            case self::$TYPE_FILE:
            default:
                return $this->removeFromFile($cache_id);
        }
    }

    /**
     * Place data into file cache
     * @param string $cache_id
     * @param $data
     * @param int $ttl
     * @return bool
     */
    private function putToFile(string $cache_id, $data, int $ttl): bool
    {
        return $this->getFileCache()->set($cache_id, $data, $ttl);
    }

    /**
     * Delete data from file cache
     * @param string $cache_id
     * @return bool
     */
    private function removeFromFile(string $cache_id): bool
    {
        $this->getFileCache()->del($cache_id);
        return true;
    }

    /**
     * Fetch data from file cache
     * @param string $cache_id
     * @return mixed|null
     */
    private function getFromFile(string $cache_id)
    {
        return $this->getFileCache()->get($cache_id);
    }

    /**
     * Place data into state
     * @param string $cache_id
     * @param $data
     * @return bool
     */
    private function putToState(string $cache_id, $data): bool
    {
        if ($this->nocache) {
            $this->getState()->del($cache_id);
            return false;
        }
        $this->getState()->set($cache_id, $data);
        return true;
    }

    /**
     * Remove data from state
     * @param string $cache_id
     * @return bool
     */
    private function removeFromState(string $cache_id): bool
    {
        $this->getState()->del($cache_id);
        return true;
    }

    /**
     * Fetch data from state
     * @param string $cache_id
     * @return mixed|null
     */
    private function getFromState(string $cache_id)
    {
        if ($this->nocache) {
            $this->getState()->del($cache_id);
            return null;
        }
        return $this->getState()->get($cache_id);
    }

    /**
     * Clear cache by type
     * @param string $type
     */
    public function clear(string $type = 'file'): void
    {
        $type = $this->getValidType($type);
        switch ($type) {
            case self::$TYPE_STATE:
                $this->clearState();
                break;
            case self::$TYPE_FILE:
            default:
                $this->clearFiles();
                break;
        }
    }

    /**
     * Remove file cache
     */
    private function clearFiles(): void
    {
        $this->getFileCache()->purgeAll();
    }

    /**
     * Remove all data from state
     */
    private function clearState(): void
    {
        $this->getState()->flushAll();
    }
}
