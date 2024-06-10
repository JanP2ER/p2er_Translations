<?php

namespace P2er\Service;

use P2er\Cache\Cache;

class Service
{
    /**
     * @var Cache
     */
    public Cache $cache;

    /**
     * @param string $url
     * @param array $params
     * @param string $user
     * @param string $password
     * @return string
     */
    public function requestGet(string $url = '', array $params = [], string $user = '', string $password = ''): string
    {
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }
        $curl = curl_init($url);
        if ($user !== '' && $password !== '') {
            curl_setopt($curl, CURLOPT_USERPWD, $user . ":" . $password);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    /**
     * @param string $url
     * @param array $params
     * @param string $user
     * @param string $password
     * @return string
     */
    public function requestPost(string $url = '', array $params = [], string $user = '', string $password = ''): string
    {
        $curl = curl_init($url);
        if ($user !== '' && $password !== '') {
            curl_setopt($curl, CURLOPT_USERPWD, $user . ":" . $password);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * Combine requests on same method with same data until
     * @param string $method
     * @param array $requestData
     * @param int $maxAge in seconds
     * @return array
     */
    public function combineRequests(string $method = '', array $requestData = [], int $maxAge = 25): array
    {
        $cacheId = get_class($this) . 'combined' . $method . md5(serialize($requestData));
        $result = [];
        // Try for 30 seconds or max age if lower
        $maxTries = min(300, $maxAge * 10);
        for ($i = 1; $i <= $maxTries; $i++) {
            // Get request from cache
            $cached = $this->cache->get($cacheId, Cache::$TYPE_FILE);
            // Use cached response
            if (is_array($cached)) {
                $result = $cached;
                break;
            }
            // Not yet started or expired?
            $cachedTime = (int)$cached;
            $now = time();
            $expired = $now - $cachedTime;
            if ($cached === null || $expired > $maxAge || $i === $maxTries) {
                // Collect requests for 30 seconds
                $this->cache->put($cacheId, $now, Cache::$TYPE_FILE, $maxAge);
                // Start request new request and place into cache once complete
                $result = $this->startRequest($method, $requestData);
                if ($result !== null) {
                    $this->cache->put($cacheId, $result, Cache::$TYPE_FILE, $maxAge);
                }
                break;
            } else {
                // Check again in 100 milliseconds
                usleep(100*1000);
            }
        }
        return (array)$result;
    }

    /**
     * Run any method on workflow service
     *
     * @param string $method
     * @param array $requestData
     * @param bool $combined
     * @return array
     */
    public function execute(
        string $method = '',
        array $requestData = [],
        bool $combined = true
    ): array {
        if ($combined) {
            $result = $this->combineRequests($method, $requestData);
        } else {
            $result = $this->startRequest($method, $requestData);
        }
        return (array)($result ?? []);
    }

    /**
     * This will run the actual method on service implementation
     *
     * @param string $method
     * @param array $requestData
     * @return ?array
     */
    public function startRequest(string $method = '', array $requestData = []): ?array
    {
        if (count($requestData) === 0) {
            return $this->$method();
        } else {
            return $this->$method($requestData);
        }
    }
}
