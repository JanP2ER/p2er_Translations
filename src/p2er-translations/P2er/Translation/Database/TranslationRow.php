<?php

namespace P2er\Translation\Database;

use P2er\Database\RowInterface;
use P2er\Utilities\DataTransfer;

require_once(__DIR__ . "/../../Utilities/DataTransfer.php");
require_once(__DIR__ . "/../../Database/RowInterface.php");

class TranslationRow implements RowInterface
{
    /**
     * Unique id for section
     * @var string
     */
    public string $id = '';

    /**
     * Used if no translation is added, only generated from external source, e.h. DeepL
     *
     * @var string
     */
    public string $fallback = '';

    /**
     * Overwrites default value
     * @var string
     */
    public string $translation = '';

    /**
     * target language
     * @var string
     */
    public string $language = '';

    /**
     * ID of parenting Group
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
