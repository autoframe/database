<?php

namespace Autoframe\Database\Orm\Action\Mysql;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\RowHelperTrait;
use PDO;

trait PdoInteractTrait
{
    use RowHelperTrait;

    //todo cache, performance logger, etc

    /**
     * @throws AfrDatabaseConnectionException
     */
    protected function getPdo(): PDO
    {
        return AfrDbConnectionManager::getInstance()->getConnectionByAlias(
            $this->getNameConnAlias()
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
        foreach ($this->getPdo()->query($sQuery)->fetchAll(PDO::FETCH_NUM) as $mRow) {
            $mRow = is_object($mRow) ? get_object_vars($mRow) : (array)$mRow;
            $aList[] = array_pop($mRow);
        }
        return $aList;
    }

    /**
     * @param string $sQuery
     * @return string|int|float|null|mixed
     * @throws AfrDatabaseConnectionException
     */
    public function getCell(string $sQuery)
    {
        $aRow = $this->getRow($sQuery);
        return count($aRow) > 0 ? array_pop($aRow) : null;
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
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function getRow(string $sQuery): array
    {
        if (
            strpos(strtoupper(substr($sQuery, -20, 20)), ' LIMIT') === false
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

    /**
     * @param string $sQuery
     * @return string|int|float|null|mixed
     * @throws AfrDatabaseConnectionException
     */
    public function oneQuery(string $sQuery)
    {
        return $this->getCell($sQuery);

    }

    /**
     * @param string $sQuery
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function valuesQuery(string $sQuery): array
    {
        return $this->getRowsValue($sQuery);

    }

    /**
     * @param string $sQuery
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function manyQuery(string $sQuery): array
    {
        return $this->getRow($sQuery);

    }

    /**
     * @param string $sQuery
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function multipleQuery(string $sQuery): array
    {
        return $this->getRowsValue($sQuery);
    }



}