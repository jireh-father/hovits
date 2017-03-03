<?php
namespace framework\library;

use framework\core\Request;
use framework\core\Router;
use framework\exception\DatabaseException;
use PDO;

class Database
{
    const PFX_CUSTOM_CACHE_KEY = '__custom_cache_key__';

    /**
     * @var Database[]
     */
    private static $conn_map = array();

    private $select_execute_time;

    /**
     * @var PDO
     */
    private $conn = null;

    /**
     * @var \PDOStatement
     */
    private $stmt = null;
    private $dsn_key = null;
    private $sql_type = null;

    private function __construct($conn, $dsn_key)
    {
        $this->conn = $conn;
        $this->dsn_key = $dsn_key;
    }

    public static function getInstance($dsn = null, $custom_cache_key = null)
    {
        if (empty($dsn)) {
            if (empty(\Config::$DEFAULT_DSN)) {
                throw new DatabaseException("DSN 정보가 없습니다.");
            }
            $dsn = \Config::$DEFAULT_DSN;
        }

        if (!empty($custom_cache_key)) {
            $dsn_key = md5(self::PFX_CUSTOM_CACHE_KEY . $custom_cache_key);
        } else {
            $dsn_key = md5(serialize($dsn));
        }

        if (empty(self::$conn_map[$dsn_key])) {
            if (empty($dsn[KEY_DSN_DB_TYPE]) || empty($dsn[KEY_DSN_HOST]) || empty($dsn[KEY_DSN_USERNAME]) || empty($dsn[KEY_DSN_PASSWORD])) {
                throw new DatabaseException("DSN 정보가 부족합니다.", $dsn);
            }

            $db_name = empty($dsn[KEY_DSN_DB_NAME]) ? "" : "dbname={$dsn[KEY_DSN_DB_NAME]};";
            $port = empty($dsn[KEY_DSN_PORT]) ? "" : "port={$dsn[KEY_DSN_PORT]};";
            $host = "{$dsn[KEY_DSN_DB_TYPE]}:{$db_name}host={$dsn[KEY_DSN_HOST]};{$port}";
            try {
                self::$conn_map[$dsn_key] = new self(new PDO($host, $dsn[KEY_DSN_USERNAME], $dsn[KEY_DSN_PASSWORD]), $dsn_key);
            } catch (\PDOException $e) {
                $exception_data = array('msg' => $e->getMessage(), 'dsn' => $dsn);
                throw new DatabaseException("DB 연결 실패.", $exception_data);
            }
        }

        return self::$conn_map[$dsn_key];
    }

    public function begin()
    {
        $ret = $this->conn->beginTransaction();
        if ($ret === false) {
            throw new DatabaseException("트랜잭션 시작 실패.", $this->conn);
        }
    }

    public function commit()
    {
        $ret = $this->conn->commit();
        if ($ret === false) {
            throw new DatabaseException("트랜잭션 커밋 실패.", $this->conn);
        }
    }

    public function rollBack()
    {
        $ret = $this->conn->rollBack();
        if ($ret === false) {
            throw new DatabaseException("트랜잭션 롤백 실패.", $this->conn);
        }
    }

    public function getSqlType()
    {
        return $this->sql_type;
    }

    public function getLastInsertId($name = null)
    {
        return $this->conn->lastInsertId($name);
    }

    public function getRowCount()
    {
        return $this->stmt->rowCount();
    }

    public function getLastErrorInfo()
    {
        return $this->stmt->errorInfo();
    }

    public function query($query, $params = null)
    {
        $query = trim($query);
        if (!$query) {
            throw new DatabaseException("query is null");
        }

        $this->sql_type = strtoupper(substr($query, 0, 6));

        $this->stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            foreach ($params as $key => &$val) {
                $this->stmt->bindParam($key + 1, $val, (is_string($val) ? PDO::PARAM_STR : PDO::PARAM_INT));
            }
        }
        Time::beginStopWatch();

        $ret = $this->stmt->execute();
        $execute_time = Time::endStopWatch();

        if ($ret === false) {
            $error_info = $this->getLastErrorInfo();
            if ($error_info[0] !== '00000') {
                $exception_data = array('error_info' => $error_info, 'query' => $query, 'params' => $params);
                throw new DatabaseException('SQL 쿼리실행 실패', $exception_data);
            }
        }

        Log::disableDb();
        if (\Config::$ENABLE_SQL_LOG_BEAUTIFIER === true) {
            $query = \SqlFormatter::format($query, false);
        }
        $log_data = array('execute_time' => $execute_time, 'query' => $query, 'params' => $params, 'trace' => getBackTrace());
        Log::info('쿼리실행', $log_data);
        Log::restoreDisableDb();

        if (PHP_WEB === true) {
            if (Request::getInstance()->getParam('_db') !== null) {
                debug($query, $params);
            }
        }

        if ($this->sql_type == 'SELECT') {
            $this->select_execute_time = $execute_time;
        }
        $this->_checkSlowQuery($execute_time, $log_data);

        return $ret;
    }

    public function fetch($fetch_mode = null)
    {
        Time::beginStopWatch($this->select_execute_time);
        $fetch_result = $this->stmt->fetchAll($fetch_mode);
        $execute_time = Time::endStopWatch();
        $this->select_execute_time = 0;
        $log_data = array('execute_time' => $execute_time, 'query' => $this->stmt->queryString, 'trace' => getBackTrace());
        $this->_checkSlowQuery($execute_time, $log_data);

        return $fetch_result;
    }

    public function fetchAll($fetch_mode = null)
    {
        Time::beginStopWatch($this->select_execute_time);
        $fetch_result = $this->stmt->fetchAll($fetch_mode);
        $execute_time = Time::endStopWatch();
        $this->select_execute_time = 0;
        $log_data = array('execute_time' => $execute_time, 'query' => $this->stmt->queryString, 'trace' => getBackTrace());
        $this->_checkSlowQuery($execute_time, $log_data);

        return $fetch_result;
    }

    private function _checkSlowQuery($execute_time, $log_data)
    {
        if (\Config::$ENABLE_SQL_SLOW_QUERY_LOG === true && $execute_time >= \Config::$LIMIT_SLOW_QUERY_SECONDS) {
            //로그 남기기
            Log::setLogType('Slow Query');
            Log::warning('Slow query is detected!', $log_data);
            Log::restoreLogType();
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function getStatement()
    {
        return $this->stmt;
    }

    public function closeConnection()
    {
        unset($this->conn);
        $this->conn = null;
        $this->dsn_key = null;
        $this->stmt = null;
        unset(self::$conn_map[$this->dsn_key]);
    }
}
