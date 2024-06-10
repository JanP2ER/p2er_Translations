<?php

namespace P2er\Utilities;

class RequestParams
{
    /**
     * @var array
     */
    public array $getParams = [];

    /**
     * @var array
     */
    public array $postParams = [];


    /**
     * Converts params to get param string on URL
     *
     * @param array $params
     * @return string
     */
    public function arrayToUrlParams(array $params): string
    {
        $urlParams = '';
        foreach ($params as $paramName => $value) {
            if ($value === null || $value === '0') {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $valueKey => $valueValue) {
                    if (!is_string($valueValue)) {
                        continue;
                    }
                    $subIdValuePair = explode(':::', $valueValue);
                    if (count($subIdValuePair) > 1) {
                        $valueValue = $subIdValuePair[1];
                    }
                    $urlParams .= '&' . $paramName . '[]=' . urlencode($valueValue);
                }
            } else {
                $urlParams .= '&' . $paramName . '=' . urlencode($value);
            }
        }
        return $urlParams;
    }

    /**
     * Fetches POST and GET parameters in one state
     *
     * @return array
     */
    public function getAllParams(): array
    {
        $params = [];
        foreach ($this->getParams as $key => $value) {
            $params[$key] = $value;
        }
        foreach ($this->postParams as $key => $value) {
            $params[$key] = $value;
        }
        return $params;
    }

    /**
     * @return array
     */
    public function getPostJson(): array
    {
        $json = file_get_contents("php://input"); // json string
        return (array)json_decode($json);
    }
}
