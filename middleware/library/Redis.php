<?php

namespace middleware\library;

if (class_exists('Predis\Client') === false) {
    require_once __DIR__ . '/Predis/lib/Predis.php';
}

use framework\exception\ApplicationException;
use Predis\Client;

class Redis
{
    /**
     * @var RedisUtil[]
     */
    private static $aInstances = array();
    private static $aDefaultDsn = array(
        'host'     => 'xxxxx',
        'port'     => 6379,
        'database' => 0
    );

    /**
     * @var \Predis\Client
     */
    private $oPredis;

    private $iLockTime = null;

    private function __construct($oPredis)
    {
        $this->oPredis = $oPredis;
    }

    /**
     * @param array $aDsn
     * @param array $aOption
     * @return Redis
     */
    public static function getInstance(array $aDsn = null, array $aOption = null)
    {
        list($aDsn, $aOption) = self::_filterParams($aDsn, $aOption);

        $sKey = self::_createKey($aDsn, $aOption);
        if (self::_exist($sKey) === false) {
            self::$aInstances[$sKey] = new self(new Client($aDsn, $aOption));
        }

        return self::$aInstances[$sKey];
    }

    public static function initialize()
    {
        foreach (self::$aInstances as $aInstance) {
            $aInstance->close();
        }
        self::$aInstances = array();
    }

    private static function _createKey($aDsn, $aOption)
    {
        return md5(serialize(array_merge($aDsn, $aOption)));
    }

    private static function _filterParams($aDsn, $aOption)
    {
        $aOption = (array)$aOption;
        if (empty($aDsn) === true) {
            $aDsn = self::$aDefaultDsn;
        } else {
            if (isset($aDsn['port']) === true) {
                $aDsn['port'] = (int)$aDsn['port'];
            }
            if (isset($aDsn['database']) === true) {
                $aDsn['database'] = (int)$aDsn['database'];
            }
        }
        ksort($aDsn);
        ksort($aOption);

        return array($aDsn, $aOption);
    }

    private static function _exist($sKey)
    {
        if (isset(self::$aInstances[$sKey]) === false) {
            return false;
        }

        return self::$aInstances[$sKey]->ping();
    }

    public function getPredis()
    {
        return $this->oPredis;
    }

    public function ping()
    {
        try {
            $bRet = $this->oPredis->ping();

            return $bRet === true;
        } catch (ApplicationException $e) {
            return false;
        }
    }

    public function select($iDbNumber)
    {
        return $this->oPredis->select($iDbNumber);
    }

    public function isLocked($sKey)
    {
        $sLockKey = $this->_getLockKey($sKey);

        return $this->get($sLockKey) == 1;
    }

    public function isAnotherLock($sKey)
    {
        $bLocked = $this->isLocked($sKey);
        if ($bLocked === false) {
            return false;
        }

        $sTimeKey = $this->_getLockTimeKey($sKey);
        $iLockTime = $this->get($sTimeKey);
        if ($this->iLockTime == $iLockTime) {
            return false;
        }

        return true;
    }

    public function set($sKey, $sVal)
    {
        if ($this->isAnotherLock($sKey) === true) {
            return false;
        }

        if (is_array($sVal) === true) {
            $sVal = json_encode($sVal);
        }

        return $this->oPredis->set($sKey, $sVal);
    }

    public function expire($sKey, $iExpire)
    {
        return $this->oPredis->expire($sKey, $iExpire);
    }

    public function keys($sKey)
    {
        return $this->oPredis->keys($sKey);
    }

    public function get($sKey)
    {
        return $this->oPredis->get($sKey);
    }

    public function del($sKey)
    {
        if ($this->isAnotherLock($sKey) === true) {
            return false;
        }

        return $this->oPredis->del($sKey);
    }

    public function exists($sKey)
    {
        return $this->oPredis->exists($sKey);
    }

    public function incr($sKey)
    {
        if ($this->isAnotherLock($sKey) === true) {
            return false;
        }

        return $this->oPredis->incr($sKey);
    }

    public function decr($sKey)
    {
        if ($this->isAnotherLock($sKey) === true) {
            return false;
        }

        return $this->oPredis->decr($sKey);
    }

    /**
     * lock은 항상 unlock과 함께 사용하세요
     * lock후 unlock을 못한 경우,
     * 무한루프에 빠지지 않도록 timout을 걸었습니다.
     *
     * @param $sKey
     * @param int $iTimeout
     * @return bool
     */
    public function lock($sKey, $iTimeout = 5)
    {
        $sLockKey = $this->_getLockKey($sKey);

        $sLock = $this->get($sLockKey);
        if ($sLock == 1) {
            if (empty($this->iLockTime) === false) {
                return false;
            }
            $iTimed = 0;
            while (true) {
                usleep(10000);
                $iTimed += 10000;
                $sLock = $this->get($sLockKey);
                if ($sLock == 0) {
                    break;
                }
                if ($iTimed >= 1000000 * $iTimeout) {
                    return false;
                }
            }
        } else {
            //lock!!
            $this->iLockTime = microtime();
            $this->incr($sLockKey);
            $this->set($this->_getLockTimeKey($sKey), $this->iLockTime);
        }

        return true;
    }

    public function unlock($sKey)
    {
        $sLockKey = $this->_getLockKey($sKey);
        $sLockTimeKey = $this->_getLockTimeKey($sKey);

        $iLock = $this->get($sLockKey);
        $iLockTime = $this->get($sLockTimeKey);

        if ($iLock == 1 && $iLockTime == $this->iLockTime) {
            $this->decr($sLockKey);
            $this->del($sLockTimeKey);
            $this->iLockTime = null;

            return true;
        }

        return false;
    }

    private function _getLockTimeKey($sKey)
    {
        return '__lock__time_' . $sKey;
    }

    private function _getLockKey($sKey)
    {
        return '__lock_' . $sKey;
    }

    public function close()
    {
        if ($this->ping() === true) {
            $this->oPredis->quit();
            $this->oPredis = null;
        }
    }

    public function getPop($sKey)
    {
        if ($this->isAnotherLock($sKey) === true) {
            return false;
        }

        $bRet = $this->exists($sKey);
        if (empty($bRet) === true) {
            return null;
        }

        $sVal = $this->get($sKey);
        $this->del($sKey);

        return $sVal;
    }
}