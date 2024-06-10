<?php

namespace P2er\Database;

abstract class DatabaseConnection
{
    /**
     * @var string
     */
    public string $table = '';

    /**
     * @var SqlConnection
     */
    public SqlConnection $sqlConnection;

    /**
     * Drop the related table
     * @return void
     */
    public function drop(): void
    {
        $table = $this->table;
        $this->sqlConnection->query("DROP TABLE $table");
    }

    /**
     * Truncate the related table. Remove all records but keep structure intact
     * @return void
     */
    public function truncate(): void
    {
        $table = $this->table;
        $truncated = $this->sqlConnection->query("TRUNCATE TABLE $table");
        if (!$truncated) {
            $error = mysqli_error($this->sqlConnection->connect());
        }
    }

    /**
     * Delete from table by id
     * @param string $id
     * @return void
     */
    public function delete(string $id = ''): bool
    {
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return false;
        }

        $table = $this->table;
        $query = "DELETE FROM $table WHERE id=?";
        $statement = $conn->prepare($query);
        $statement->bind_param("i", $id);
        $statement->execute();
        $statement->close();
        return true;
    }

    /**
     * List of currently available ids in database
     * @return string[]
     */
    public function getIds(): array
    {
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return [];
        }
        $ids = [];
        $table = $this->table;
        $query = "SELECT id FROM $table";
        $statement = $conn->prepare($query);
        $statement->execute();
        $questionsResult = $statement->get_result();
        if ($questionsResult && $questionsResult->num_rows > 0) {
            while ($row = $questionsResult->fetch_assoc()) {
                $ids[] = $row['id'];
            }
        }
        $statement->close();
        return $ids;
    }

    /**
     * Insert new row or replace existing if matched by primary keys
     * @param RowInterface $row
     * @param bool $replace if exists
     * @return bool
     */
    public function insert(RowInterface $row, bool $replace = false): string
    {
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return false;
        }
        $table = $this->table;
        $rowVars = get_object_vars($row);
        $values = array_values($rowVars);
        $properties = array_keys($rowVars);
        $placeholders = array_map(fn($property): string => '?', $properties);
        $rowProps = join(', ', $properties);
        $rowPlaceholders = join(', ', $placeholders);
        $typeList = array_map(fn($rowVar): string => $this->typeToBind(gettype($rowVar)), $rowVars);

        $insertOrReplace = 'INSERT';
        if ($replace) {
            $insertOrReplace = 'REPLACE';
        }
        $query = "$insertOrReplace INTO $table ($rowProps) VALUES ($rowPlaceholders)";
        $stmt = $conn->prepare($query);
        if (is_bool($stmt)) {
            $error = mysqli_error($this->sqlConnection->connect());
            return $stmt;
        }
        $types = join('', array_values($typeList));
        $stmt->bind_param(
            $types,
            ...$values
        );
        $stmt->execute();
        $error = mysqli_error($this->sqlConnection->connect());
        $stmt->close();
        return $error === '';
    }

    /**
     * Update only if row exists
     * @param RowInterface $row
     * @return bool
     */
    public function update(RowInterface $row): bool
    {
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return false;
        }
        $table = $this->table;
        $rowVars = get_object_vars($row);
        unset($rowVars['id']);
        $values = array_values($rowVars);
        $properties = array_keys($rowVars);
        $setters = array_map(fn($property): string => $property . '=?', $properties);
        $rowSetters = join(', ', $setters);
        $typesList = array_map(fn($rowVar): string => $this->typeToBind(gettype($rowVar)), $rowVars);
        $query = "UPDATE $table SET $rowSetters WHERE id=?";
        $stmt = $conn->prepare($query);
        $types = join('', array_values($typesList)) . 's';
        $values[] = $row->id;
        $stmt->bind_param(
            $types,
            ...$values
        );
        $stmt->execute();
        $stmt->close();
        return true;
    }

    /**
     * List of all rows in table
     * @return RowInterface[]
     */
    public function getAll(): array
    {
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return [];
        }
        $rows = [];
        $table = $this->table;
        $query = "SELECT * FROM $table";
        $statement = $conn->prepare($query);
        $statement->execute();
        $questionsResult = $statement->get_result();
        if ($questionsResult && $questionsResult->num_rows > 0) {
            while ($row = $questionsResult->fetch_assoc()) {
                // Convert row will allow explicit row definition for custom database table interface
                $rows[] = $this->convertRow($row);
            }
        }
        $statement->close();
        return $rows;
    }

    /**
     * List of rows in database for id
     * @param string $id
     * @return RowInterface[]
     */
    public function getById(string $id): array
    {
        if (!$id) {
            return [];
        }
        $conn = $this->sqlConnection->connect();
        if (!$conn) {
            return [];
        }
        $rows = [];
        $table = $this->table;
        $query = "SELECT * FROM $table WHERE id=?";
        $statement = $conn->prepare($query);
        $statement->bind_param("s", $id);
        $statement->execute();
        $statementResult = $statement->get_result();
        if ($statementResult && $statementResult->num_rows > 0) {
            while ($row = $statementResult->fetch_assoc()) {
                $rows[] = $this->convertRow($row);
            }
        }
        $statement->close();
        return $rows;
    }

    /**
     * @param array $row
     * @return RowInterface
     */
    abstract protected function convertRow(array $row): RowInterface;

    /**
     * @param $type
     * @return string
     */
    public function typeToBind($type): string
    {
        switch ($type) {
            case 'float':
            case 'double':
                return 'd';
            case 'boolean':
            case 'integer':
                return 'i';
            case 'array':
            case 'object':
                return 'b';
            case 'string':
            default:
                return 's';
        }
    }
}
