<?php

namespace P2er\Translation\Database;

use P2er\Database\RowInterface;
use P2er\Utilities\DataTransfer;

require_once(__DIR__ . "/../../Utilities/DataTransfer.php");
require_once(__DIR__ . "/../../Database/RowInterface.php");

class GroupRow implements RowInterface
{
    /**
     * Unique id for section
     * @var string
     */
    public string $id = '';

    /**
     * Speaking Group Name
     *
     * @var string
     */
    public string $label = '';

    /**
     * Parent ID for Group Allocation
     * @var string
     */
    public string $parent = '';

    /**
     * Index for sorting
     * @var string
     */
    public string $index = "0";

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        // Move data from source to type safe reference
        DataTransfer::arrayToObject($data, $this);
    }
}
