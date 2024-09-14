<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

interface DbActionInterface extends AfrOrmBlueprintInterface, EscapeInterface
{
// todo: de descompus / mutat din AfrOrmActionInterface \ use Doctrine\DBAL\Types\Types;
//todo: https://stackoverflow.com/questions/2934258/how-do-i-get-the-current-time-zone-of-mysql

    public function dXXbGetDefaultCharsetAndCollation(string $sDbName): array; //SHOW CHARACTER SET;

    public function dXXbSetDefaultCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool;

//https://www.db4free.net/




    /** Poate fac aici o singura metoda cu cheia sa fie numele db-ului la returen */
    public static function tblListAll(string $sDbFrom, string $sLike = ''): array;
    public static function tblListAllWithProperties(string $sDbFrom, string $sLike = ''): array;

    public static function tblExists(string $sTblName, string $sDbName): bool;
    public static function tblGetDefaultCharsetAndCollation(string $sTblName,string $sDbName): array; //SHOW CHARACTER SET;
    public static function tblSetDefaultCharsetAndCollation(string $sTblName,string $sDbName, string $sCharset, string $sCollation = ''): bool;


    public static function tblCreate(string $sTblName,string $sInsideDbName, array $aOptions = []): bool;
    // SHOW CREATE DATABASE|TABLE ****
    // CREATE DATABASE `testswmb4` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci */
    public static function tblCreateCharset(
        string $sTblName,
        string $sInsideDbName,
        string $sCharset = 'utf8',
        string $sCollate = 'utf8_general_ci',
        array  $aOptions = []
    ): bool;
    public static function tblRename(string $sTblFrom, string $sTblTo, string $sDbFrom, string $sDbTo = '' ): bool;
    public static function tblMoveTableFromDb1ToDb2(string $sTableName, string $sDbFrom, string $sDbTo): bool;
    public static function tblCopyTable(string $sTableNameFrom, string $sTableNameTo, string $sDbFrom, string $sDbTo = ''): bool;

}