<?php

namespace P2er\Database;

require_once(__DIR__ . "/./SqlTables.php");
require_once(__DIR__ . "/./SqlTableStructures.php");

class SqlConnection
{
    /**
     * @var \mysqli|null
     */
    private ?\mysqli $mysqli = null;

    /**
     * @return \mysqli|null
     */
    public function connect(): ?\mysqli
    {
        // Open connection only once
        if ($this->mysqli !== null) {
            return $this->mysqli;
        }

        // Basic connection settings
        $databaseHost = getenv('DB_HOST');
        $databaseUsername = getenv('DB_USER');
        $databasePassword = getenv('DB_PASSWORD');
        $databaseName = getenv('DB_NAME');

        // Connect to the database
        $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);
        if ($mysqli !== false) {
            $this->mysqli = $mysqli;
        }
        return $this->mysqli;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if ($this->mysqli !== null) {
            mysqli_close($this->mysqli);
            $this->mysqli = null;
        }
    }

    /**
     * @return void
     */
    public function setup(): void
    {
        $mysqli = $this->connect();
        if ($mysqli !== null) {
            $sqlTables = new SqlTables();
            $sqlTableStructures = new SqlTableStructures();
            $tables = $sqlTables->getTables();
            $tableStructures = $sqlTableStructures->getTableStructures();
            foreach ($tables as $key => $table) {
                $tableExists = $this->query("DESCRIBE `$table`");
                $tableStructure = $tableStructures[$key];
                // Update existing table structure if table definition exists
                if ($tableExists !== false) {
                    // Strip structure from definitions that are unwanted in modify query
                    $modifyStructureNoDefault = str_replace(["DEFAULT ''", "DEFAULT NULL"], "", $tableStructure);
                    $modifyStructureNoPrimaryKeyNoBrackets = preg_replace("/(^\s*\(|\)\s*$|,?\s*.*PRIMARY KEY\s*\(.*\))/m", '', $modifyStructureNoDefault);
                    $modifyStructureArr = explode(',', $modifyStructureNoPrimaryKeyNoBrackets);
                    $modifyStructure = 'MODIFY ' . join(', MODIFY ', $modifyStructureArr);
                    // Update table
                    $tableModified = $this->query("ALTER TABLE $table $modifyStructure");
                    if (!$tableModified) {
                        $error = mysqli_error($mysqli);
                    }
                    continue;
                } else {
                    $tableCreated = $this->query("CREATE TABLE $table $tableStructure");
                    if (!$tableCreated) {
                        $error = mysqli_error($mysqli);
                    }
                }
                if (!$error) {
                    // Exists or has been created without error :)
                }
            }
        }
    }

    /**
     * @param string $query
     * @return \mysqli_result|bool
     */
    public function query(string $query = '')
    {
        $mysqli = $this->connect();
        if ($mysqli === null) {
            return false;
        }
        return mysqli_query($mysqli, $query);
    }
}
