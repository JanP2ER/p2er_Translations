<?php

namespace P2er\Utilities;

class DataTransfer
{
    /**
     * @param array $src
     * @param object $target
     * @return object
     */
    public static function arrayToObject(array $src, object $target)
    {
        if (count($src) <= 0) {
            return $target;
        }

        foreach ($src as $key => $value) {
            if (!property_exists($target, $key)) {
                continue;
            }
            if ($value === null) {
                continue;
            }

            $type = gettype($target->$key);
            switch ($type) {
                case 'boolean':
                    $target->$key = (bool)($value);
                    break;
                case 'integer':
                    $target->$key = (int)($value);
                    break;
                case 'double':
                    $target->$key = (float)($value);
                    break;
                case 'string':
                    $target->$key = (string)($value);
                    break;
                case 'array':
                    $target->$key = (array)($value);
                    break;
                case 'object':
                    $target->$key = (object)($value);
                    break;
                default:
                    continue 2;
            }
        }
        return $target;
    }
}
