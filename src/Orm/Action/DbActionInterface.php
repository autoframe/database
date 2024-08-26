<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Doctrine\DBAL\Types\Types;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

interface DbActionInterface extends AfrOrmBlueprintInterface
{
    /*

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

     * */
    /*
     *     //echo '#'.get_class($obj).'#'.spl_object_id($obj).'#'.spl_object_hash($obj);
    public function withPDO(\PDO $oPDO): self;
    public function withDbName(string $sDbName): self;
    public function withTableName(string $sTableName): self;

    public static function pdoGetAllCharsets(): array; //SHOW CHARACTER SET;     //SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC;
    public static function pdoGetAllCollations(): array; //SHOW COLLATION     //SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC;
    public static function pdoSetDefaultCharsetAndCollation(string $sCharset, string $sCollation = ''): bool; //SHOW CHARACTER SET;



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
    public static function withConnAlias(string $sAlias): DbActionInterface;


    /** Poate fac aici o singura metoda cu cheia sa fie numele db-ului la returen */
    public function dbListAll(string $sDbNameLike = ''): array; //SHOW DATABASES
    public function dbListAllWithProperties(string $sDbNameLike = ''): array;


    public function dbExists(string $sDbName): bool;

    public function dbGetDefaultCharsetAndCollation(string $sDbName): array; //SHOW CHARACTER SET;
    public function dbSetDefaultCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool;



    // CREATE DATABASE IF NOT EXISTS `!!!!!!!!` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
    public function dbCreateUsingDefaultCharset(string $sDbName, array $aOptions = [],bool $bIfNotExists = false): bool;
    // CREATE DATABASE `testswmb4` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci */
    public function dbCreateUsingCharset(
        string $sDbName,
        string $sCharset = 'utf8',
        string $sCollate = 'utf8_general_ci',
        array  $aOptions = [],
        bool $bIfNotExists = false
    ): bool;

//https://stackoverflow.com/questions/67093/how-do-i-rename-a-mysql-database-change-schema-name
//SELECT CONCAT('RENAME TABLE admin_new.', table_name, ' TO NNNEEEWWWW.', table_name, '; ') FROM information_schema.TABLES WHERE table_schema='admin_new';
//    public function dbRename(string $sDbFrom, string $sDbTo): bool;

    /**
     * @param string $sLike
     * @param bool $bWildcard
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function dbListCollation(string $sLike = '', bool $bWildcard = false): array;


}