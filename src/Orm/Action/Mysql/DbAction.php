<?php

namespace Autoframe\Database\Orm\Action\Mysql;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\CnxActionInterface;
use Autoframe\Database\Orm\Action\DbActionInterface;
use Autoframe\Database\Orm\Action\DbActionSingletonTrait;

class DbAction implements DbActionInterface
{
    use PdoInteractTrait;
    use EscapeTrait;
    use Syntax;
    use DbActionSingletonTrait;


    public function getInstanceConnAlias(): CnxActionInterface
    {
        return $this->oCnxAction;
    }

    public function dbGetCharsetAndCollation(): array
    {
        return $this->oCnxAction->cnxDbGetCharsetAndCollation($this->getNameDatabase());
    }

    public function dbSetCharsetAndCollation(string $sCharset, string $sCollation = ''): bool
    {
        return $this->oCnxAction->cnxDbSetCharsetAndCollation($this->getNameDatabase(), $sCharset, $sCollation);
    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function dbGetTblList(string $sLike = ''): array
    {
        $query = "SELECT TABLE_NAME
              FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = " . static::encapsulateCellValue($this->getNameDatabase());
        if ($sLike) {
            $query .= " AND TABLE_NAME LIKE " . static::encapsulateCellValue($sLike);
        }

        return $this->valuesQuery($query);
    }

    public function dbGetTblListWithCharset(string $sLike = ''): array
    {
        $query = "SELECT * 
              FROM `information_schema`.`TABLES`
              WHERE `TABLE_SCHEMA` = " . static::encapsulateCellValue($this->getNameDatabase());

        if ($sLike) {
            $query .= " AND `TABLE_NAME` LIKE " . static::encapsulateCellValue($sLike);
        }
        $aRows = $this->getAllRows($query, 'TABLE_NAME');
        foreach ($aRows as &$aRow) {
            $sCharset = explode('_', $aRow['TABLE_COLLATION'] ?? '')[0];
            $sCharset = !empty($sCharset) ? $sCharset : 'utf8';
            $aRow = array_merge(
                [
                    self::CON_ALIAS => $this->getNameConnAlias(),
                    self::DB_NAME => $this->getNameDatabase(),
                    self::TBL_NAME => $aRow['TABLE_NAME'],
                    self::CHARSET => $sCharset,
                    self::COLLATION => $aRow['TABLE_COLLATION'] ?? $sCharset . '_general_ci',
                    self::ENGINE => $aRow['ENGINE'] ?? 'MyISAM',
                    self::COMMENT => $aRow['TABLE_COMMENT'] ?? '',
                ],
                $aRow
            );
        }
        return $aRows;


    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function dbShowCreateTable(string $sTblName): string
    {
        $this->trimTblName($sTblName, true);

        //SHOW CREATE TABLE dms_new.dms_file;
        $sQuery = 'SHOW CREATE TABLE ' . static::encapsulateDbTblColName($this->getNameDatabase());
        $sQuery .= '.' . static::encapsulateDbTblColName($sTblName);
        $mRow = $this->getPdo()->query($sQuery)->fetch(\PDO::FETCH_ASSOC);
        $mRow = is_object($mRow) ? get_object_vars($mRow) : (array)$mRow;
        return end($mRow);
    }

    /**
     * @param string $sTblName
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbTblExists(string $sTblName): bool
    {
        $this->trimTblName($sTblName, false);
        if(strlen($sTblName)<1){
            return false;
        }
        $query = "SELECT TABLE_NAME
              FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = " . static::encapsulateCellValue($this->getNameDatabase());
        $query .= ' AND TABLE_NAME = ' . static::encapsulateCellValue($sTblName) . ' LIMIT 1';
        return (bool)$this->oneQuery($query);
    }

    /**
     * @param string $sTblName
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function dbGetTblCharsetAndCollation(string $sTblName): array
    {
        $this->trimTblName($sTblName, true);

        $aResults = $this->dbGetTblListWithCharset($sTblName);
        if (empty($aResults[$sTblName])) {
            return [];
        }
        return [
            self::CHARSET => $aResults[$sTblName][self::CHARSET],
            self::COLLATION => $aResults[$sTblName][self::COLLATION],
        ];

    }

    /**
     * @param string $sTblName
     * @param string $sCharset
     * @param string $sCollation
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbSetTblCharsetAndCollation(string $sTblName, string $sCharset, string $sCollation = ''): bool
    {
        $this->trimTblName($sTblName, true);

        $aCollationList = $this->oCnxAction->cnxGetAllCollationCharsets($sCharset, true);
        if (empty($sCollation)) {
            $sCollation = $sCharset . '_general_ci';
        }
        if (!isset($aCollationList[$sCollation])) {
            foreach (array_keys($aCollationList, $sCharset) as $sCollateX) {
                if (strpos($sCollateX, '_general_ci') !== false) {
                    $sCollation = $sCollateX;
                    $sCharset = $aCollationList[$sCollation];
                    break;
                }
            }
        }
        if (!isset($aCollationList[$sCollation])) { //fallback
            $sCharset = 'utf8';
            $sCollation = 'utf8_general_ci';
        }
        $sCollationPart = $sCollation ? "COLLATE $sCollation" : '';
        $query = 'ALTER TABLE ' .
            static::encapsulateDbTblColName($this->getNameDatabase()) . '.' .
            static::encapsulateDbTblColName($sTblName) .
            " CHARACTER SET $sCharset $sCollationPart";
        return (bool)$this->execPdoStatement($query);
    }

    public function dbCreateTbl(string $sTblName, string $sCharset = 'utf8mb4', string $sCollate = 'utf8mb4_general_ci', array $aOptions = []): bool
    {
        // TODO: Implement dbCreateTbl() method.
    }

    /**
     * @param string $sTblFrom
     * @param string $sTblTo
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbRenameTbl(string $sTblFrom, string $sTblTo): bool
    {
        $this->trimTblName($sTblFrom, true);
        $this->trimTblName($sTblTo, true);

        if ($this->dbTblExists($sTblFrom)) {
            $sDb = static::encapsulateDbTblColName($this->getNameDatabase()) . '.';
            return (bool)$this->execPdoStatement(
                'RENAME TABLE ' .
                $sDb . static::encapsulateDbTblColName($sTblFrom) . ' TO ' .
                $sDb . static::encapsulateDbTblColName($sTblTo)
            );
        } else {
            throw new AfrDatabaseConnectionException(
                "Table '$sTblFrom' does not exist in database '" . $this->getNameDatabase() . "'"
            );
        }
    }


    /**
     * @param string $sTableNameFrom
     * @param string $sTableNameTo
     * @param $mOtherDatabase
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbCopyTable(string $sTableNameFrom, string $sTableNameTo, $mOtherDatabase = null): bool
    {
        $this->trimTblName($sTableNameFrom, true);
        if (!$this->dbTblExists($sTableNameFrom)) {
            throw new AfrDatabaseConnectionException(
                "Table '$sTableNameFrom' does not exist in database '" . $this->getNameDatabase() . "'"
            );
        }
       /* if($mOtherDatabase){
            if(is_string($mOtherDatabase)){
                $this->trimDbName($mOtherDatabase, true);
                if(!$this->getInstanceConnAlias()->cnxDatabaseExists($mOtherDatabase)){
                    throw new AfrDatabaseConnectionException(
                        "Database '$mOtherDatabase' does not exist in connection '" . $this->getNameConnAlias() . "'"
                    );
                }

                return (bool)$this->execPdoStatement(
                    'RENAME TABLE ' .
                    static::encapsulateDbTblColName($this->getNameDatabase()) . '.' .
                    static::encapsulateDbTblColName($sTableName) . ' TO ' .
                    static::encapsulateDbTblColName($mOtherDatabase) . '.' .
                    static::encapsulateDbTblColName($sTableName)
                );
            }
            elseif(is_object($mOtherDatabase)){
                // todo cross tech copy to other db, and drop current table after checks
                throw new AfrDatabaseConnectionException('TODO: implement cross tech move for dbMoveTableToOtherDatabase() method.');
            }
            throw new AfrDatabaseConnectionException('Invalid parameter received for the target database in '.__METHOD__);

        }
        else{
            $this->dbShowCreateTable($sTableNameFrom);

            //select all rows
        }*/

    }

    /**
     * @param string $sTableName
     * @param $mOtherDatabase
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbMoveTableToOtherDatabase(string $sTableName, $mOtherDatabase): bool
    {
        //cross tech database :D
        // TODO: Implement dbMoveTableToOtherDatabase() method.
        $this->trimTblName($sTableName, true);
        if (!$this->dbTblExists($sTableName)) {
            throw new AfrDatabaseConnectionException(
                "Table '$sTableName' does not exist in database '" . $this->getNameDatabase() . "'"
            );
        }
        if(is_string($mOtherDatabase)){
            $this->trimDbName($mOtherDatabase, true);
            if(!$this->getInstanceConnAlias()->cnxDatabaseExists($mOtherDatabase)){
                throw new AfrDatabaseConnectionException(
                    "Database '$mOtherDatabase' does not exist in connection '" . $this->getNameConnAlias() . "'"
                );
            }

            return (bool)$this->execPdoStatement(
                    'RENAME TABLE ' .
                    static::encapsulateDbTblColName($this->getNameDatabase()) . '.' .
                    static::encapsulateDbTblColName($sTableName) . ' TO ' .
                    static::encapsulateDbTblColName($mOtherDatabase) . '.' .
                    static::encapsulateDbTblColName($sTableName)
                );
        }
        elseif(is_object($mOtherDatabase)){
            // todo cross tech copy to other db, and drop current table after checks
            throw new AfrDatabaseConnectionException('TODO: implement cross tech move for dbMoveTableToOtherDatabase() method.');
        }
        throw new AfrDatabaseConnectionException('Invalid parameter received for the target database in '.__METHOD__);

    }

    /**
     * @param string $sTableName
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbEmptyTable(string $sTableName): bool
    {
        $this->trimTblName($sTableName, true);
        return (bool)$this->execPdoStatement(
            'TRUNCATE TABLE ' .
            static::encapsulateDbTblColName($this->getNameDatabase()) . '.' .
            static::encapsulateDbTblColName($sTableName)
        );
    }

    /**
     * Drops a table from the database.
     *
     * @param string $sTableName The name of the table to be dropped.
     * @return bool Returns true if the table was successfully dropped, false otherwise.
     *
     * @throws AfrDatabaseConnectionException Thrown if there is an issue with the database connection.
     */
    public function dbDropTable(string $sTableName): bool
    {
        $this->trimTblName($sTableName, true);
        return (bool)$this->execPdoStatement(
            'DROP TABLE IF EXISTS ' .
            static::encapsulateDbTblColName($this->getNameDatabase()) . '.' .
            static::encapsulateDbTblColName($sTableName)
        );
    }
}