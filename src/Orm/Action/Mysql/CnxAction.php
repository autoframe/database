<?php

namespace Autoframe\Database\Orm\Action\Mysql;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\CnxActionInterface;
use Autoframe\Database\Orm\Action\AfrPdoAliasSingletonTrait;
use PDO;

class CnxAction implements CnxActionInterface
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
     * The response array should contain the keys: self::DB_NAME, self::CHARSET, self::COLLATION
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function CnxListAllDatabasesWithProperties(string $sDbNameLike = ''): array
    {
        $this->trimDbName($sDbNameLike,false);

        $aRows = $this->getAllRows(
            'SELECT * FROM `information_schema`.`SCHEMATA`' . (
            strlen($sDbNameLike) ? ' WHERE `SCHEMA_NAME` LIKE ' . self::encapsulateCellValue($sDbNameLike) : ''
            ), 'SCHEMA_NAME');
        foreach ($aRows as &$aRow) {
            $aRow[self::DB_NAME] = $aRow['SCHEMA_NAME'];
            $aRow[self::CHARSET] = $aRow['DEFAULT_CHARACTER_SET_NAME'] ?? 'utf8';
            $aRow[self::COLLATION] = $aRow['DEFAULT_COLLATION_NAME'] ?? $aRow[self::CHARSET].'_general_ci';
        }
        return $aRows;
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
        $aList = $this->CnxListAllDatabasesWithProperties($sDbName);
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

        $aUtf = $this->CnxGetCollationCharsetList('utf8%general_ci',false);
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
        string $sCharset = 'utf8mb4',
        string $sCollate = 'utf8mb4_general_ci',
        array $aOptions = [],
        bool $bIfNotExists = false
    ): bool
    {
        $this->trimDbName($sDbName,true);

        $aCollationList = $this->CnxGetCollationCharsetList($sCharset,true);
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

        $sComment = $aOptions['comment'] ?? ($aOptions['COMMENT'] ?? null); //todo:test
        if ($sComment !== null) {
            $sSvVersion = self::getCell('SELECT VERSION()');
            $aSvVersion=explode('.',explode('-',$sSvVersion)[0]);
            $iMajor = (int)$aSvVersion[0];
            $iMinor = !empty($aSvVersion[1]) ? (int)$aSvVersion[1] : 0;
            if( stripos($sSvVersion,'MariaDB') &&  ($iMajor===10 && $iMinor>=5 || $iMajor>10) ){
                $sComment = ' COMMENT ' . self::encapsulateCellValue($sComment); // >= MariaDb 10.5.0
                // information_schema.schemata SCHEMA_COMMENT
            }
            else{
                $sComment = '';
            }

        }

        if($this->dbExists($sDbName)){
            $sSql = 'ALTER DATABASE ';
            $sSql.= self::encapsulateDbTblColName($sDbName);
            $sSql.= " CHARACTER SET $sCharset COLLATE $sCollate ";
            $sSql.= $sComment;
        }
        else{
            $sSql = 'CREATE DATABASE '.($bIfNotExists ? 'IF NOT EXISTS ' : '');
            $sSql.= self::encapsulateDbTblColName($sDbName);
            $sSql.= " /*!40100 DEFAULT CHARACTER SET $sCharset COLLATE $sCollate $sComment */ ";
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
    public function CnxGetCollationCharsetList(string $sLike = '', bool $bWildcard = null): array
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

    /**
     * Retrieves all available character sets from the database.
     *
     * @return array An array of character set names.
     * @throws AfrDatabaseConnectionException If there is an error connecting to the database.
     */
    public function pdoGetAllCharsets(): array
    {
        return $this->getRowsValue(
            'SELECT CHARACTER_SET_NAME FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SET_NAME` DESC'
        );
    }

    /**
     * Retrieves all the collations from the database.
     *
     * @return array An array containing all the collations.
     * @throws AfrDatabaseConnectionException If there is an issue with the database connection.
     */
    public function pdoGetAllCollations(): array
    {
        return $this->getRowsValue(
            'SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC'
        );    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function cnxSetDefaultCharsetAndCollation(
        string $sCharset='utf8mb4',
        string $sCollation = 'utf8mb4_0900_ai_ci',
        bool $character_set_server = true,
        bool $character_set_database = false
    ): bool
    {
/*
OLD: SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'
show variables like 'character_set%';
SHOW VARIABLES LIKE 'collation_%';
+--------------------------+-------------------------------------+
| Variable_name            | Value                               |
+--------------------------+-------------------------------------+
| character_set_client     | utf8mb4                             |
| character_set_connection | utf8mb4                             |
| character_set_results    | utf8mb4                             |
| character_set_database   | utf8mb4                             | # this is not permitted on Cpanel  (8.0.39 - MySQL Community Server - GPL) #1227 - Access denied; you need (at least one of) the SUPER, SYSTEM_VARIABLES_ADMIN or SESSION_VARIABLES_ADMIN privilege(s) for this operation
| character_set_server     | utf8mb4                             |
| character_set_system     | utf8mb3                             |
| character_set_filesystem | binary                              |
| character_sets_dir       | /usr/share/percona-server/charsets/ |
+--------------------------+-------------------------------------+
*/

        $aResults = [
            $this->execPdoStatement("SET NAMES $sCharset" . ($sCollation ? " COLLATE $sCollation" : '')) ? 1 : 0
        ];
        if($character_set_server){
            $aResults[] = $this->execPdoStatement("SET character_set_server = $sCharset;") ? 1 : 0;
        }
        if($character_set_database){
            $aResults[] = $this->execPdoStatement("SET character_set_database = $sCharset;") ? 1 : 0;
        }
        return array_sum($aResults)===count($aResults);

    }
}