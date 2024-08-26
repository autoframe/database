<?php

namespace Autoframe\Database\Orm\Action\Mysql;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\DbActionInterface;
use Autoframe\Database\Orm\Action\AfrPdoAliasSingletonTrait;
use PDO;

class DbAction implements DbActionInterface
{
    use PdoInteractTrait;
    use Encapsulate;
    use Syntax;
    use AfrPdoAliasSingletonTrait;


    /**
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function dbListAll(string $sDbNameLike = ''): array
    {
        $this->trimDbName($sDbNameLike,false);
        return $this->getRowsValue(
            'SHOW DATABASES' . (
            strlen($sDbNameLike) ? ' LIKE ' . self::encapsulateCellValue($sDbNameLike) : ''
            )
        );
    }

    /**
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function dbListAllWithProperties(string $sDbNameLike = ''): array
    {
        $this->trimDbName($sDbNameLike,false);
        return $this->getAllRows(
            'SELECT * FROM `information_schema`.`SCHEMATA`' . (
            strlen($sDbNameLike) ? ' WHERE `SCHEMA_NAME` LIKE ' . self::encapsulateCellValue($sDbNameLike) : ''
            ), 'SCHEMA_NAME');
    }

    /**
     * @param string $sDbName
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbExists(string $sDbName): bool
    {
        $this->trimDbName($sDbName,true);
        return in_array($sDbName,$this->dbListAll($sDbName));
    }

    /**
     * @param string $sDbName
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function dbGetDefaultCharsetAndCollation(string $sDbName): array
    {
        $this->trimDbName($sDbName,true);
        $aList = $this->dbListAllWithProperties($sDbName);
        return [
            static::DB_NAME => isset($aList[$sDbName]) ? $sDbName : null,
            static::CHARSET => $aList[$sDbName]['DEFAULT_CHARACTER_SET_NAME'] ?? null,
            static::COLLATION => $aList[$sDbName]['DEFAULT_COLLATION_NAME'] ?? null,
        ];
    }

    /**
     * @param string $sDbName
     * @param string $sCharset
     * @param string $sCollation
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbSetDefaultCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool
    {
        $this->trimDbName($sDbName,true);
        return $this->dbCreateUsingCharset($sDbName, $sCharset, $sCollation, [], true);
    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function dbCreateUsingDefaultCharset(string $sDbName, array $aOptions = [], bool $bIfNotExists = false): bool
    {
        $this->trimDbName($sDbName,true);

        $aUtf = $this->dbListCollation('utf8%general_ci',false);
        $sCharset = $aUtf['utf8mb4_general_ci'] ?? ($aUtf['utf8_general_ci'] ?? 'utf8');
        $sCollate = $sCharset.'_general_ci';
        return $this->dbCreateUsingCharset($sDbName, $sCharset, $sCollate, $aOptions, $bIfNotExists);
    }

    //sql_mode=NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION

//SET @@sql_mode := REPLACE(@@sql_mode, 'NO_ZERO_DATE', '');
//SET GLOBAL sql_mode = 'modes';
//SET SESSION sql_mode = 'modes';
#init-connect=\'SET NAMES utf8\'
#collation_server=utf8_unicode_ci
#character_set_server=utf8
#character-set-server=utf8mb4
#collation-server=utf8mb4_general_ci


    /**
     * @param string $sDbName
     * @param string $sCharset
     * @param string $sCollate
     * @param array $aOptions
     * @param bool $bIfNotExists
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function dbCreateUsingCharset(
        string $sDbName,
        string $sCharset = 'utf8',
        string $sCollate = 'utf8_general_ci',
        array $aOptions = [],
        bool $bIfNotExists = false
    ): bool
    {
        $this->trimDbName($sDbName,true);

        $aCollationList = $this->dbListCollation($sCharset,true);
        if(!isset($aCollationList[$sCollate])){
            $aCollationListMatchingCharset = array_keys($aCollationList, $sCharset);
            foreach ($aCollationListMatchingCharset as $sCollateX) {
                if(strpos($sCollateX,'_general_ci') !== false){
                    $sCollate = $sCollateX;
                    $sCharset = $aCollationList[$sCollate];
                    break;
                }
            }
            if(!isset($aCollationList[$sCollate])){ //fallback
                $sCharset = 'utf8';
                $sCollate = 'utf8_general_ci';
            }

        }

        //CREATE DATABASE IF NOT EXISTS `admin_new` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
        //CREATE DATABASE `EmailRejection` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */

        if($this->dbExists($sDbName)){
            $sSql = 'ALTER DATABASE ';
            $sSql.= self::encapsulateDbTblColName($sDbName);
            $sSql.= " CHARACTER SET $sCharset COLLATE $sCollate ";
        }
        else{
            $sSql = 'CREATE DATABASE '.($bIfNotExists ? 'IF NOT EXISTS ' : '');
            $sSql.= self::encapsulateDbTblColName($sDbName);
            $sSql.= " /*!40100 DEFAULT CHARACTER SET $sCharset COLLATE $sCollate */ ";
        }
        //ALTER DATABASE databasename CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        //ALTER TABLE tablename CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        //Or if you're still on MySQL 5.5.2 or older which didn't support 4-byte UTF-8, use utf8 instead of utf8mb4:
        //ALTER DATABASE databasename CHARACTER SET utf8 COLLATE utf8_unicode_ci;
        //ALTER TABLE tablename CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;

        return (bool)$this->execPdoStatement($sSql);

    }

    //https://stackoverflow.com/questions/67093/how-do-i-rename-a-mysql-database-change-schema-name
    //SELECT CONCAT('RENAME TABLE admin_new.', table_name, ' TO NNNEEEWWWW.', table_name, '; ') FROM information_schema.TABLES WHERE table_schema='admin_new';
   // public function dbRename(string $sDbFrom, string $sDbTo): bool { }

    /**
     * @param string $sLike
     * @param bool $bWildcard
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function dbListCollation(string $sLike = '', bool $bWildcard = null): array
    {
        $sSqlLike = '';
        if(strlen($sLike)){
            if($bWildcard === null && strpos($sLike, '_') === false){
                $bWildcard = true;
            }
            $sLike = $bWildcard ? '%'.$sLike.'%' : $sLike;
            $sSqlLike = ' WHERE `COLLATION_NAME` LIKE ' . self::encapsulateCellValue($sLike);
        }
        $aResults = [];
        foreach ($this->getAllRows(
            'SELECT * FROM `information_schema`.`COLLATIONS` '.$sSqlLike.' ORDER BY CHARACTER_SET_NAME,COLLATION_NAME LIMIT 1000')
        as $row){
            $aResults[$row['COLLATION_NAME']] = $row['CHARACTER_SET_NAME'];
        }
        return $aResults;

    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    protected function trimDbName(string &$sDbName, bool $bErrorOnEmpty)
    {
        $sDbName = trim($sDbName);
        if ($bErrorOnEmpty && strlen($sDbName) < 1) {
            throw new AfrDatabaseConnectionException('Database name is empty');
        }
    }

}