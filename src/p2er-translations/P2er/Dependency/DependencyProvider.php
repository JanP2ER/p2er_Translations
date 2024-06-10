<?php

namespace P2er\Dependency;

use Cu\Translation\Service\DeepLService;
use P2er\Cache\Cache;
use P2er\Database\SqlConnection;
use P2er\Translation\Database\DeepLDatabase;
use P2er\Translation\Database\TranslationTable;
use P2er\Translation\Database\GroupTable;
use P2er\Utilities\RequestParams;

class DependencyProvider
{
    /**
     * @var DependencyProvider
     */
    protected static DependencyProvider $instance;

    /**
     * Avoid external instantiation
     * DependencyProvider constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return DependencyProvider
     */
    public static function getInstance(): DependencyProvider
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var DeepLService
     */
    private DeepLService $deepLService;

    /**
     * @return DeepLService
     */
    public function getDeepLService(): DeepLService
    {
        if (isset($this->deepLService)) {
            return $this->deepLService;
        }

        require_once(__DIR__ . "/../Translation/Service/DeepLService.php");
        $this->deepLService = new DeepLService();
        $this->deepLService->cache = $this->getCache();
        $this->deepLService->deepLDatabase = $this->getDeepLDatabase();
        return $this->deepLService;
    }

    /**
     * @var DeepLDatabase
     */
    private DeepLDatabase $deepLDatabase;

    /**
     * @return DeepLDatabase
     */
    public function getDeepLDatabase(): DeepLDatabase
    {
        if (isset($this->deepLDatabase)) {
            return $this->deepLDatabase;
        }

        require_once(__DIR__ . "/../Translation/Database/DeepLDatabase.php");
        $this->deepLDatabase = new DeepLDatabase();
        $this->deepLDatabase->sqlConnection = $this->getSqlConnection();
        return $this->deepLDatabase;
    }

    /**
     * @var Cache
     */
    private Cache $cache;

    /**
     * @return Cache
     */
    public function getCache(): Cache
    {
        if (isset($this->cache)) {
            return $this->cache;
        }

        require_once(__DIR__ . "/../Cache/Cache.php");
        $this->cache = new Cache(($this->getRequestParams()->getAllParams()['nocache'] ?? '0') === '1');
        $this->cache->prefix = 'translations';
        return $this->cache;
    }

    /**
     * @var SqlConnection
     */
    private SqlConnection $sqlConnection;

    /**
     * @return SqlConnection
     */
    public function getSqlConnection(): SqlConnection
    {
        if (isset($this->sqlConnection)) {
            return $this->sqlConnection;
        }

        require_once(__DIR__ . "/../Database/SqlConnection.php");
        $this->sqlConnection = new SqlConnection();
        return $this->sqlConnection;
    }

    /**
     * @var RequestParams
     */
    private RequestParams $requestParams;

    /**
     * @return RequestParams
     */
    public function getRequestParams(): RequestParams
    {
        if (isset($this->requestParams)) {
            return $this->requestParams;
        }

        require_once(__DIR__ . "/../Utilities/RequestParams.php");
        $this->requestParams = new RequestParams();
        $this->requestParams->getParams = $_GET;
        $this->requestParams->postParams = $_POST;
        return $this->requestParams;
    }

    /**
     * @var TranslationTable
     */
    private TranslationTable $translationTable;

    /**
     * @return TranslationTable
     */
    public function getTranslationTable(): TranslationTable
    {
        if (isset($this->translationTable)) {
            return $this->translationTable;
        }

        require_once(__DIR__ . "/../Translation/Database/TranslationTable.php");
        $this->translationTable = new TranslationTable();
        $this->translationTable->sqlConnection = $this->getSqlConnection();
        return $this->translationTable;
    }

    /**
     * @var GroupTable
     */
    private GroupTable $groupTable;

    /**
     * @return GroupTable
     */
    public function getGroupTable(): GroupTable
    {
        if (isset($this->groupTable)) {
            return $this->groupTable;
        }

        require_once(__DIR__ . "/../Translation/Database/GroupTable.php");
        $this->groupTable = new GroupTable();
        $this->groupTable->sqlConnection = $this->getSqlConnection();
        return $this->groupTable;
    }
}
