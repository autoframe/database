<?php

namespace Autoframe\Database\Orm\Action;

use Doctrine\DBAL\Types\Types;

interface AfrOrmActionInterface
{
    //echo '#'.get_class($obj).'#'.spl_object_id($obj).'#'.spl_object_hash($obj);
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

    /** Poate fac aici o singura metoda cu cheia sa fie numele db-ului la returen */
    public static function dbListAll(string $sDbNameLike = ''): array;
    public static function dbListAllWithProperties(string $sDbNameLike = ''): array;


    public static function dbExists(string $sDbName): bool;

    public static function dbGetDefaultCharsetAndCollation(string $sDbName): array; //SHOW CHARACTER SET;
    public static function dbSetDefaultCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool;



    // CREATE DATABASE IF NOT EXISTS `!!!!!!!!` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
    public static function dbCreateUsingDefaultCharset(string $sDbName, array $aOptions = [],bool $bIfNotExists = false): bool;
    // CREATE DATABASE `testswmb4` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci */
    public static function dbCreateUsingCharset(
        string $sDbName,
        string $sCharset = 'utf8',
        string $sCollate = 'utf8_general_ci',
        array  $aOptions = [],
        bool $bIfNotExists = false
    ): bool;

//https://stackoverflow.com/questions/67093/how-do-i-rename-a-mysql-database-change-schema-name
//SELECT CONCAT('RENAME TABLE admin_new.', table_name, ' TO NNNEEEWWWW.', table_name, '; ') FROM information_schema.TABLES WHERE table_schema='admin_new';
//    public static function dbRename(string $sDbFrom, string $sDbTo): bool;

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

    /** Poate fac aici o singura metoda cu cheia sa fie numele db-ului la returen */
    public static function colListAll(string $sDbFrom, string $sTblFrom): array;
    public static function colListAllWithProperties(string $sDbFrom, string $sTblFrom): array;

    public static function colExists(string $sColName,string $sTblName, string $sDbName): bool;
    public static function colRename(string $sColNameFrom,string $sColNameTo,string $sTblName, string $sDbName): bool;

    //flags primary key
    public static function colCreateType(string $sColName, string $sTblTo, string $sDb, string $sDataType, array $aOptions = []): bool;
    public static function colCreateInt(string $sColName, string $sTblTo, string $sDb, string $sDataType = Types::INTEGER, array $aOptions = []): bool;
    // SHOW CREATE DATABASE|TABLE ****
    // CREATE DATABASE `testswmb4` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci */
    public static function colCreateFull(
        string $sColName,
        string $sType,
        string $sCharset = 'utf8',
        string $sCollate = 'utf8_general_ci',
        array  $aOptions = []
    ): bool;

    /**
    protected function createIndexName($type, array $columns)
    {
        $index = strtolower($this->prefix.$this->table.'_'.implode('_', $columns).'_'.$type);

        return str_replace(['-', '.'], '_', $index);
    }

    protected function addColumnDefinition($definition)
    {
        $this->columns[] = $definition;

        if ($this->after) {
            $definition->after($this->after);

            $this->after = $definition->name;
        }

        return $definition;
    }

    public function after($column, Closure $callback)
    {
        $this->after = $column;

        $callback($this);

        $this->after = null;
    }
     */


/*
#1075 - Incorrect table definition; there can be only one auto column and it must be defined as a key
ALTER TABLE `muta#ble` ADD CONSTRAINT `fkmut1` FOREIGN KEY (`fkid`) REFERENCES `mutable`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
\n\t\v  sunt inlocuite in ''

CREATE TABLE if not exist `muta#ble` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fkid` int(11) NOT NULL,
  `int_defa``ult'_none_unsigned` int(10) unsigned NOT NULL,
  `int_default_none_signed` int(10) unsigned zerofill NOT NULL,
  `int_default_none_null` int(11) DEFAULT NULL,
  `int_default_null_null` int(11) DEFAULT NULL,
  `1b_tinyint` TINYINT(4) NOT NULL DEFAULT '22',
  `2b_smallint` smallint(6) NOT NULL,
  `3b_mediumint` mediumint(9) NOT NULL,
  `8b_bigint` bigint(20) NOT NULL,
  `decimalX` decimal(10,2) NOT NULL,
  `floatX` float NOT NULL,
  `double_floatX2` double NOT NULL,
  `date` date NOT NULL,
  `dt` datetime NOT NULL,
  `t` time NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `char_0_255_padded_with_spaces` char(2) NOT NULL,
  `varchar_0-65535` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tinytxt_2_1` tinytext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `txt_2_2` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `medtxt_2_3` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `longtxt_2_4` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `binary_as_chr_but_01` binary(4) NOT NULL,
  `varbinary_as_varchr_but_01` varbinary(6) NOT NULL,
  `tinyblob_2_1` tinyblob DEFAULT NULL COMMENT 'defau''lt tr"ebui`e s)a fi(e null',
  `blob_2_16` blob DEFAULT NULL COMMENT 'default trebuie sa fie null',
  `medblob_2_24` mediumblob DEFAULT NULL COMMENT 'default trebuie sa fie null',
  `longblob_2_32` longblob DEFAULT NULL COMMENT 'default trebuie sa fie null',
  `enum_64k` enum('a','b','c','') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `set_max_64_vals` set('d','e','f','') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `1b_tinyint` (`1b_tinyint`),
  KEY `fkmut1` (`fkid`),
  KEY `2b_smallint` (`2b_smallint`),
  CONSTRAINT `fkmut1` FOREIGN KEY (`fkid`) REFERENCES `mutable` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='yha-comment!';

--
-- Dumping data for table `mutable`
--

INSERT INTO `mutable` (`id`, `int_default_none_unsigned`, `int_default_none_signed`, `int_default_none_null`, `int_default_null_null`, `1b_tinyint`, `2b_smallint`, `3b_mediumint`, `8b_bigint`, `decimalX`, `floatX`, `double_floatX2`, `date`, `dt`, `t`, `ts`, `char_0_255_padded_with_spaces`, `varchar_0-65535`, `tinytxt_2_1`, `txt_2_2`, `medtxt_2_3`, `longtxt_2_4`, `binary_as_chr_but_01`, `varbinary_as_varchr_but_01`, `tinyblob_2_1`, `blob_2_16`, `medblob_2_24`, `longblob_2_32`, `enum_64k`, `set_max_64_vals`, `json`) VALUES
(2, 3, 0000000007, NULL, NULL, 1, 2, 3, 8, '3.37', 3.37646, 3.3764645353, '2024-05-16', '2024-05-15 23:04:56', '00:08:57', '2024-05-15 21:07:31', 'a', 'b', 'r', 'r', 'r', 'r', 0x01010000, 0x010101, NULL, NULL, NULL, NULL, 'b', 'd,f', '{\"YHA\":true}');
COMMIT;

REPLACE INTO `mutable` (`id`, `int_default_none_unsigned`, `int_default_none_signed`, `int_default_none_null`, `int_default_null_null`, `1b_tinyint`, `2b_smallint`, `3b_mediumint`, `8b_bigint`, `decimalX`, `floatX`, `double_floatX2`, `date`, `dt`, `t`, `ts`, `char_0_255_padded_with_spaces`, `varchar_0-65535`, `tinytxt_2_1`, `txt_2_2`, `medtxt_2_3`, `longtxt_2_4`, `binary_as_chr_but_01`, `varbinary_as_varchr_but_01`, `tinyblob_2_1`, `blob_2_16`, `medblob_2_24`, `longblob_2_32`, `enum_64k`, `set_max_64_vals`, `json`) VALUES
(2, 3, 0000000007, NULL, NULL, 1, 2, 3, 8, '3.37', 3.37646, 3.3764645353, '2024-05-16', '2024-05-15 23:04:56', '00:08:57', '2024-05-15 21:07:31', 'a', 'b', 'r', 'r', 'r', 'r', 0x01010000, 0x010101, NULL, NULL, NULL, NULL, 'b', 'd,f', '{\"YHA\":true}');

 * */

}