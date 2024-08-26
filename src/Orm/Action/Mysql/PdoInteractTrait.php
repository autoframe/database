<?php

namespace Autoframe\Database\Orm\Action\Mysql;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\AfrPdoHelperTrait;
use PDO;

trait PdoInteractTrait
{
    use AfrPdoHelperTrait;

    //todo cache, performance logger, etc

    /**
     * @throws AfrDatabaseConnectionException
     */
    protected function getPdo(): PDO
    {
        return AfrDbConnectionManager::getInstance()->getConnectionByAlias(
            $this->sConnAlias
        );
    }

    /**
     * Retrieves multiple rows having a single value for each row
     * Same as array values or oneMultipleQuery
     *
     * @param string $sQuery
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function getRowsValue(string $sQuery): array
    {
        $aList = [];
        $pdo = $this->getPdo();
        foreach ($pdo->query($sQuery)->fetchAll(PDO::FETCH_NUM) as $mRow) {
            $mRow = is_object($mRow) ? get_object_vars($mRow) : (array)$mRow;
            $aList[] = array_pop($mRow);
        }
        return $aList;
    }

    /**
     * @param string $sQuery
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function getCell(string $sQuery): array
    {
        $aRow = $this->getRow($sQuery);
        return array_pop($aRow);
    }

    /** Multiple query
     *
     * @param string $sQuery
     * @param string $sIndexColumn
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function getAllRows(string $sQuery, string $sIndexColumn = 'id'): array
    {
        $aList = [];
        foreach ($this->getPdo()->query($sQuery)->fetchAll(PDO::FETCH_ASSOC) as $mRow) {
            $mRow = is_object($mRow) ? get_object_vars($mRow) : (array)$mRow;
            if (isset($mRow[$sIndexColumn])) {
                $aList[$mRow[$sIndexColumn]] = $mRow;
            } else {
                $aList[] = $mRow;
            }
        }
        return $aList;
    }

    /** Many query
     *
     * @param string $sQuery
     * @param int $iLimit
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function getRow(string $sQuery, int $iLimit = 1): array
    {
        if (
            $iLimit &&
            strpos(strtoupper(substr($sQuery, -20, 20)), 'LIMIT') === false
        ) {
            $sQuery .= ' LIMIT 1';
        }

        $mRow = $this->getPdo()->query($sQuery)->fetch(PDO::FETCH_ASSOC);
        return is_object($mRow) ? get_object_vars($mRow) : (array)$mRow;
    }

    /**
     * @param string $sQuery
     * @return false|int
     * @throws AfrDatabaseConnectionException
     */
    public function execPdoStatement(string $sQuery)
    {
        return $this->getPdo()->exec($sQuery);
    }

}