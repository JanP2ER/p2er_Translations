<?php

namespace P2er\Translation\Database;

use P2er\Database\DatabaseConnection;
use P2er\Database\RowInterface;
use P2er\Database\SqlTables;

require_once(__DIR__ . '/GroupRow.php');
require_once(__DIR__ . '/../../Database/SqlTables.php');
require_once(__DIR__ . '/../../Database/DatabaseConnection.php');

class GroupTable extends DatabaseConnection
{
    /**
     * @var string
     */
    public string $table = SqlTables::GROUPS;

    /**
     * Converts generic row to explicit interface implementation
     * @param array $row
     * @return RowInterface
     */
    protected function convertRow(array $row): RowInterface
    {
        return new GroupRow($row);
    }

    /**
     * List of currently available vehicles in database
     * @param string $id
     * @return ?GroupRow
     */
    public function getByPrimaryKeys(string $id): ?GroupRow
    {
        if (!$id) {
            return null;
        }
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return null;
        }
        $row = null;
        $table = $this->table;
        $query = "SELECT * FROM $table WHERE id=?";
        $statement = $conn->prepare($query);
        if (is_bool($statement)) {
            $error = mysqli_error($this->sqlConnection->connect());
            return null;
        }
        $statement->bind_param("ss", $id);
        $statement->execute();
        $questionsResult = $statement->get_result();
        if ($questionsResult && $questionsResult->num_rows > 0) {
            $row = $questionsResult->fetch_assoc();
            $row = new GroupRow($row);
        }
        $statement->close();
        return $row;
    }
}
