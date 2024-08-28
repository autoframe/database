<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

interface CnxActionInterface extends AfrOrmBlueprintInterface
{

    //SET FOREIGN_KEY_CHECKS=0;
    //SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
    //START TRANSACTION;
    //SET time_zone = "+00:00";
    //...
    //SET FOREIGN_KEY_CHECKS=1;
    //COMMIT;

    /*


    //USE db_name; SELECT @@character_set_database, @@collation_database;
    //SELECT * FROM `information_schema`.`COLLATIONS` ORDER BY `COLLATIONS`.`IS_DEFAULT` DESC, `COLLATIONS`.`COLLATION_NAME` DESC;


    public function escapeDbTableName($sName_Database_Table_Column): string;
    public function escapeDbColumnName($sName_Database_Table_Column): string;

    public function escapeValueAsMixed($mValue);
    public function escapeValueAsString($mValue):string;

    /**
     * @param string $sAlias
     * @return string FQCN
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAlias(string $sAlias): CnxActionInterface;

    /**
     * @param string $sDbNameLike filter database name like or %startsWith or containing %part%
     * @return array
     */
    public function dbListAll(string $sDbNameLike = ''): array;

    /**
     * The response array should contain the keys: self::DB_NAME, self::CHARSET, self::COLLATION
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function CnxListAllDatabasesWithProperties(string $sDbNameLike = ''): array;


    public function dbExists(string $sDbName): bool;

    public function dbGetDefaultCharsetAndCollation(string $sDbName): array; //SHOW CHARACTER SET;

    public function dbSetDefaultCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool;


    public function dbCreateUsingDefaultCharset(string $sDbName, array $aOptions = [], bool $bIfNotExists = false): bool;

    public function dbCreateUsingCharset(
        string $sDbName,
        string $sCharset = 'utf8mb4',
        string $sCollate = 'utf8mb4_general_ci',
        array  $aOptions = [],
        bool   $bIfNotExists = false
    ): bool;


    /**
     * @param string $sLike
     * @param bool $bWildcard
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function CnxGetCollationCharsetList(string $sLike = '', bool $bWildcard = false): array;


    /**
     * Retrieves all available character sets from the database.
     *
     * @return array An array of character set names.
     * @throws AfrDatabaseConnectionException If there is an error connecting to the database.
     */
    public function pdoGetAllCharsets(): array; //SHOW CHARACTER SET;     //SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC;

    /**
     * Retrieves all the collations from the database.
     *
     * @return array An array containing all the collations.
     * @throws AfrDatabaseConnectionException If there is an issue with the database connection.
     */
    public function pdoGetAllCollations(): array; //SHOW COLLATION     //SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC;

    public function cnxSetDefaultCharsetAndCollation(string $sCharset='utf8mb4',
                                                     string $sCollation = 'utf8mb4_0900_ai_ci',
                                                     bool $character_set_server = true,
                                                     bool $character_set_database = false
    ): bool;



}