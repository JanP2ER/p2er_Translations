<?php

namespace P2er\Translation\Database;

use P2er\Database\DatabaseConnection;
use P2er\Database\RowInterface;
use P2er\Database\SqlTables;

require_once(__DIR__ . '/TranslationRow.php');
require_once(__DIR__ . '/../../Database/SqlTables.php');
require_once(__DIR__ . '/../../Database/DatabaseConnection.php');

class TranslationTable extends DatabaseConnection
{
    /**
     * @var string
     */
    public string $table = SqlTables::TRANSLATIONS;

    /**
     * Converts generic row to explicit interface implementation
     * @param array $row
     * @return RowInterface
     */
    protected function convertRow(array $row): RowInterface
    {
        return new TranslationRow($row);
    }

    /**
     * List of currently available translations in database
     * @param string $id
     * @param string $language
     * @return ?TranslationRow
     */
    public function getByPrimaryKeys(string $id, string $language): ?TranslationRow
    {
        if (!$id || !$language) {
            return null;
        }
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return null;
        }
        $row = null;
        $table = $this->table;
        $query = "SELECT * FROM $table WHERE id=? AND language=?";
        $statement = $conn->prepare($query);
        if (is_bool($statement)) {
            $error = mysqli_error($this->sqlConnection->connect());
            return null;
        }
        $statement->bind_param("ss", $id,  $language);
        $statement->execute();
        $questionsResult = $statement->get_result();
        if ($questionsResult && $questionsResult->num_rows > 0) {
            $row = $questionsResult->fetch_assoc();
            $row = new TranslationRow($row);
        }
        $statement->close();
        return $row;
    }

    /**
     * Translation regardless of formal
     * @param string $fallback
     * @param string $language
     * @return ?TranslationRow
     */
    public function getByFallback(string $fallback = '', string $language = ''): ?TranslationRow
    {
        if ($fallback === '') {
            return null;
        }
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return null;
        }
        $row = null;
        $table = $this->table;
        $query = "SELECT * FROM $table WHERE fallback=? AND language=?";
        $statement = $conn->prepare($query);
        if (is_bool($statement)) {
            $error = mysqli_error($this->sqlConnection->connect());
            return null;
        }
        $statement->bind_param("ss", $fallback, $language);
        $statement->execute();
        $questionsResult = $statement->get_result();
        if ($questionsResult && $questionsResult->num_rows > 0) {
            $row = $questionsResult->fetch_assoc();
            $row = new TranslationRow($row);
        }
        $statement->close();
        return $row;
    }
}
