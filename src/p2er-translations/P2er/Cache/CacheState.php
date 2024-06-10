<?php

namespace P2er\Cache;

class CacheState
{
    /**
     * @var array
     */
    private array $state = [];

    /**
     * @param string $id
     * @return mixed|null
     */
    public function get(string $id)
    {
        return $this->state[$id] ?? null;
    }

    /**
     * @param string $id
     * @param $data
     * @return mixed
     */
    public function set(string $id, $data)
    {
        return $this->state[$id] = $data;
    }

    /**
     * @param string $id
     */
    public function del(string $id): void
    {
        if (isset($this->state[$id])) {
            unset($this->state[$id]);
        }
    }

    public function flushAll(): void
    {
        $this->state = [];
    }
}
