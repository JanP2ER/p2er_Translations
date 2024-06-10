<?php

namespace P2er\Database;

class SqlTableStructures
{
    public const DEEPL = "(
                          `id` varchar(64) NOT NULL DEFAULT '',
                          `text` longtext DEFAULT NULL,
                          `translation` longtext DEFAULT NULL,
                          `language` varchar(64) NOT NULL DEFAULT '',
                          `formal` varchar(64) NOT NULL DEFAULT '',
                          PRIMARY KEY(id)
                        )";

    public const TRANSLATIONS = "(
                          `id` varchar(128) NOT NULL DEFAULT '',
                          `fallback` longtext DEFAULT NULL DEFAULT '',
                          `translation` longtext DEFAULT NULL DEFAULT '',
                          `language` varchar(64) NOT NULL DEFAULT '',
                          `parent` varchar(128) NOT NULL DEFAULT '',
                          `index` integer NOT NULL DEFAULT 0,
                          PRIMARY KEY(id,language)
                        )";

    public const GROUPS = "(
                          `id` varchar(128) NOT NULL DEFAULT '',
                          `label` varchar(256) NOT NULL DEFAULT '',
                          `parent` varchar(128) NOT NULL DEFAULT '',
                          `index` integer NOT NULL DEFAULT 0,
                          PRIMARY KEY(id)
                        )";

    /**
     * Get all table structures
     * @return array
     */
    public function getTableStructures(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getConstants();
    }

    /**
     * @param $type
     * @return string
     */
    public static function getColumnType($type): string
    {
        switch ($type) {
            case 'boolean':
                return 'tinyint(1)';
            case 'integer':
                return 'int(11)';
                break;
            case 'double':
                return 'float';
            case 'string':
            case 'array':
            case 'object':
            default:
                return 'longtext';
        }
    }
}
