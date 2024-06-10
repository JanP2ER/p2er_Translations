<?php

namespace P2er\Translation\Database;

use P2er\Database\RowInterface;
use P2er\Utilities\DataTransfer;

require_once(__DIR__ . "/../../Utilities/DataTransfer.php");
require_once(__DIR__ . "/../../Database/RowInterface.php");

class DeepLRow implements RowInterface
{
    /**
     * vehicle identifier
     * from text md5 with target language
     * @var string
     */
    public string $id = '';

    /**
     * to translate
     * @var string
     */
    public string $text = '';

    /**
     * translation
     * @var string
     */
    public string $translation = '';

    /**
     * target language
     * @var string
     */
    public string $language = '';

    /**
     * @var string
     */
    public string $formal = '';

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        // Move data from source to type safe reference
        DataTransfer::arrayToObject($data, $this);
    }
}
