<?php

namespace P2er\Translation\Database;

use P2er\Database\DatabaseConnection;
use P2er\Database\RowInterface;
use P2er\Database\SqlTables;

require_once(__DIR__ . '/DeepLRow.php');
require_once(__DIR__ . '/../../Database/SqlTables.php');
require_once(__DIR__ . '/../../Database/DatabaseConnection.php');

class DeepLDatabase extends DatabaseConnection
{
    /**
     * @var string
     */
    public string $table = SqlTables::DEEPL;

    /**
     * @param array $row
     * @return RowInterface
     */
    protected function convertRow(array $row): RowInterface
    {
        return new DeepLRow($row);
    }

    /**
     * Translation regardless of formal
     * @param string $text
     * @param string $language
     * @return ?DeepLRow
     */
    public function getByText(string $text = '', string $language = ''): ?DeepLRow
    {
        if ($text === '') {
            return null;
        }
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return null;
        }
        $row = null;
        $table = $this->table;
        $query = "SELECT * FROM $table WHERE text=? AND language=?";
        $statement = $conn->prepare($query);
        if (is_bool($statement)) {
            $error = mysqli_error($this->sqlConnection->connect());
            return null;
        }
        $statement->bind_param("ss", $text, $language);
        $statement->execute();
        $questionsResult = $statement->get_result();
        if ($questionsResult && $questionsResult->num_rows > 0) {
            $row = $questionsResult->fetch_assoc();
            $row = new DeepLRow($row);
        }
        $statement->close();
        return $row;
    }
}
