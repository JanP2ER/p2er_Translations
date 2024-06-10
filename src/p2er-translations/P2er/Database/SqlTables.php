<?php

namespace P2er\Database;

class SqlTables
{
    public const DEEPL ='p2er_deepl';
    public const TRANSLATIONS = 'p2er_translations';
    public const GROUPS = 'p2er_groups';

    /**
     * Get all tables
     * @return array
     */
    public function getTables(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getConstants();
    }
}
