<?php

namespace Autoframe\Database\Orm\Action\Mysql;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\CnxActionInterface;
use Autoframe\Database\Orm\Action\CnxActionSingletonTrait;

class CnxAction implements CnxActionInterface
{
    use PdoInteractTrait;
    use EscapeTrait;
    use Syntax;
    use CnxActionSingletonTrait;


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
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function cnxGetAllDatabaseNames(string $sDbNameLike = ''): array
    {
        $this->trimDbName($sDbNameLike, false);
        return $this->getRowsValue(
            'SHOW DATABASES' . (
            strlen($sDbNameLike) ? ' LIKE ' . static::encapsulateCellValue($sDbNameLike) : ''
            )
        );
    }

    /**
     * The response array should contain the keys: self::DB_NAME, self::CHARSET, self::COLLATION
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function cnxGetAllDatabaseNamesWithProperties(string $sDbNameLike = ''): array
    {
        $this->trimDbName($sDbNameLike, false);

        $aRows = $this->getAllRows(
            'SELECT * FROM `information_schema`.`SCHEMATA`' . (
            strlen($sDbNameLike) ? ' WHERE `SCHEMA_NAME` LIKE ' . static::encapsulateCellValue($sDbNameLike) : ''
            ), 'SCHEMA_NAME');
        foreach ($aRows as &$aRow) {
            $aRow[self::DB_NAME] = $aRow['SCHEMA_NAME'];
            $aRow[self::CHARSET] = $aRow['DEFAULT_CHARACTER_SET_NAME'] ?? 'utf8';
            $aRow[self::COLLATION] = $aRow['DEFAULT_COLLATION_NAME'] ?? $aRow[self::CHARSET] . '_general_ci';
        }
        return $aRows;
    }

    /**
     * @param string $sDbName
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function cnxDatabaseExists(string $sDbName): bool
    {
        $this->trimDbName($sDbName, true);
        return in_array($sDbName, $this->cnxGetAllDatabaseNames($sDbName));
    }

    /**
     * @param string $sDbName
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function cnxDbGetDefaultCharsetAndCollation(string $sDbName): array
    {
        $this->trimDbName($sDbName, true);
        $aList = $this->cnxGetAllDatabaseNamesWithProperties($sDbName);
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
    public function cnxDbSetDefaultCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool
    {
        $this->trimDbName($sDbName, true);
        return $this->cnxCreateDatabaseUsingCharset($sDbName, $sCharset, $sCollation, [], true);
    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function cnxCreateDatabaseUsingDefaultCharset(string $sDbName, array $aOptions = [], bool $bIfNotExists = false): bool
    {
        $this->trimDbName($sDbName, true);

        $aUtf = $this->cnxGetAllCollationCharsets('utf8%general_ci', false);
        $sCharset = $aUtf['utf8mb4_general_ci'] ?? ($aUtf['utf8_general_ci'] ?? 'utf8');
        $sCollate = $sCharset . '_general_ci';
        return $this->cnxCreateDatabaseUsingCharset($sDbName, $sCharset, $sCollate, $aOptions, $bIfNotExists);
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
    public function cnxCreateDatabaseUsingCharset(
        string $sDbName,
        string $sCharset = 'utf8mb4',
        string $sCollate = 'utf8mb4_general_ci',
        array  $aOptions = [],
        bool   $bIfNotExists = false
    ): bool
    {
        $this->trimDbName($sDbName, true);

        $aCollationList = $this->cnxGetAllCollationCharsets($sCharset, true);
        if (!isset($aCollationList[$sCollate])) {
            $aCollationListMatchingCharset = array_keys($aCollationList, $sCharset);
            foreach ($aCollationListMatchingCharset as $sCollateX) {
                if (strpos($sCollateX, '_general_ci') !== false) {
                    $sCollate = $sCollateX;
                    $sCharset = $aCollationList[$sCollate];
                    break;
                }
            }
            if (!isset($aCollationList[$sCollate])) { //fallback
                $sCharset = 'utf8';
                $sCollate = 'utf8_general_ci';
            }

        }

        //CREATE DATABASE IF NOT EXISTS `admin_new` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
        //CREATE DATABASE `EmailRejection` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */

        $sComment = $aOptions['comment'] ?? ($aOptions['COMMENT'] ?? null); //todo:test
        if ($sComment !== null) {
            $sSvVersion = self::getCell('SELECT VERSION()');
            $aSvVersion = explode('.', explode('-', $sSvVersion)[0]);
            $iMajor = (int)$aSvVersion[0];
            $iMinor = !empty($aSvVersion[1]) ? (int)$aSvVersion[1] : 0;
            if (stripos($sSvVersion, 'MariaDB') && ($iMajor === 10 && $iMinor >= 5 || $iMajor > 10)) {
                $sComment = ' COMMENT ' . static::encapsulateCellValue($sComment); // >= MariaDb 10.5.0
                // information_schema.schemata SCHEMA_COMMENT
            } else {
                $sComment = '';
            }

        }

        if ($this->cnxDatabaseExists($sDbName)) {
            $sSql = 'ALTER DATABASE ';
            $sSql .= self::encapsulateDbTblColName($sDbName);
            $sSql .= " CHARACTER SET $sCharset COLLATE $sCollate ";
            $sSql .= $sComment;
        } else {
            $sSql = 'CREATE DATABASE ' . ($bIfNotExists ? 'IF NOT EXISTS ' : '');
            $sSql .= self::encapsulateDbTblColName($sDbName);
            $sSql .= " /*!40100 DEFAULT CHARACTER SET $sCharset COLLATE $sCollate $sComment */ ";
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
    public function cnxGetAllCollationCharsets(string $sLike = '', bool $bWildcard = null): array
    {
        $sSqlLike = '';
        if (strlen($sLike)) {
            if ($bWildcard === null && strpos($sLike, '_') === false) {
                $bWildcard = true;
            }
            $sLike = $bWildcard ? '%' . $sLike . '%' : $sLike;
            $sSqlLike = ' WHERE `COLLATION_NAME` LIKE ' . static::encapsulateCellValue($sLike);
        }
        $aResults = [];
        foreach ($this->getAllRows(
            'SELECT * FROM `information_schema`.`COLLATIONS` ' . $sSqlLike . ' ORDER BY CHARACTER_SET_NAME,COLLATION_NAME LIMIT 1000')
                 as $row) {
            $aResults[$row['COLLATION_NAME']] = $row['CHARACTER_SET_NAME'];
        }
        return $aResults;

    }



    /**
     * Retrieves all available character sets from the database.
     *
     * @return array An array of character set names.
     * @throws AfrDatabaseConnectionException If there is an error connecting to the database.
     */
    public function cnxGetAllCharsets(): array
    {
        ///SHOW CHARACTER SET;
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
    public function cnxGetAllCollations(): array
    {
        //SHOW COLLATION
        return $this->getRowsValue(
            'SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC'
        );
    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function cnxSetConnectionCharsetAndCollation(
        string $sCharset = 'utf8mb4',
        string $sCollation = 'utf8mb4_0900_ai_ci',
        bool   $character_set_server = true,
        bool   $character_set_database = false
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
        if ($character_set_server) {
            $aResults[] = $this->execPdoStatement("SET character_set_server = $sCharset;") ? 1 : 0;
        }
        if ($character_set_database) {
            $aResults[] = $this->execPdoStatement("SET character_set_database = $sCharset;") ? 1 : 0;
        }
        return array_sum($aResults) === count($aResults);

    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function cnxShowCreateDatabase(string $sDbName): string
    {
        $aRow = $this->getRow('SHOW CREATE DATABASE ' . self::encapsulateDbTblColName($sDbName));
        return $aRow['Create Database'] ?? end($aRow);
    }

    //https://stackoverflow.com/questions/2934258/how-do-i-get-the-current-time-zone-of-mysql
    //https://phoenixnap.com/kb/change-mysql-time-zone
    //https://www.db4free.net/
    /**
     * @param string $sTimezone +00:00 or +02:00 ...
     * @return bool
     * @throws AfrDatabaseConnectionException
     */
    public function cnxSetTimezone(string $sTimezone = '+00:00'): bool
    {
        return (bool)$this->execPdoStatement(
            'SET time_zone = ' . $this->escapeValueAsMixed(empty($sTimezone) ? '+00:00' : $sTimezone)
        );
// timezone values can be given in several formats, none of which are case-sensitive:
//    As the value 'SYSTEM', indicating that the server time zone is the same as the system time zone.
//    As a string indicating an offset from UTC of the form [H]H:MM, prefixed with a + or -, such as '+10:00', '-6:00', or '+05:30'. A leading zero can optionally be used for hours values less than 10; MySQL prepends a leading zero when storing and retrieving the value in such cases. MySQL converts '-00:00' or '-0:00' to '+00:00'.
//    This value must be in the range '-13:59' to '+14:00', inclusive.
//    As a named time zone, such as 'Europe/Helsinki', 'US/Eastern', 'MET', or 'UTC'.
    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    public function cnxGetTimezone(int $iSnapHourInto = 2): string //'+00:00';
    {
        //SELECT @@global.time_zone as g, @@session.time_zone as s ,NOW() as n
        $sServerNow = $this->getCell('SELECT NOW()');
        $iUTC = strtotime($sServerNow.' UTC');
        //$iUTC+=60*16;

        $delta = $iUTC-time();

        $sign = $delta<0?'-':'+';

        $hours = floor(abs($delta) / 3600);

        $minutes = floor((abs($delta) / 60) % 60);

        $iSnapHourInto = max(min(abs($iSnapHourInto),60),1);//max 60 part as minutes

        $hourParts = ceil(60 / $iSnapHourInto);
        $minutes_snap_to_15 = abs(ceil(($minutes - $hourParts / 2) / $hourParts));
        if ($minutes_snap_to_15 > $iSnapHourInto - 1) {
            $hours++;
            $minutes_snap_to_15 = 0;
        }
        $minutes = $minutes_snap_to_15 * $hourParts;

        return $sign.
            ($hours <=9 ? "0" . $hours : $hours).':'.
            ($minutes <=9 ? "0" . $minutes : $minutes);
    }
}