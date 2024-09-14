<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Orm\Action\Mysql\Convert;
use Autoframe\Database\Orm\Action\ConvertFacade as ConvertSwitch;
use PHPUnit\Framework\TestCase;
use Autoframe\Database\Orm\Action\CnxActionFacade;

class AfrOrmHelperTest extends TestCase
{

    public static function insideProductionVendorDir(): bool
    {
        return strpos(__DIR__, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false;
    }

    protected function setUp(): void
    {

    }

    protected function tearDown(): void
    {
        //cleanup between tests for static
    }

    public static function extractQuotProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            [
                "'test'  ",
                "'",
                0,
                'mysql',
                [
                    Convert::QUOTED => "'test'",
                    Convert::COL => 'test',
                    Convert::END_OFFSET => 5,
                ]
            ], [
                " 'test'  ",
                "'",
                1,
                'mysql',
                [
                    Convert::QUOTED => "'test'",
                    Convert::COL => 'test',
                    Convert::END_OFFSET => 6,
                ]
            ],
            [
                ' "x"',
                "",
                1,
                'mysql',
                [
                    Convert::QUOTED =>  '"x"',
                    Convert::COL => 'x',
                    Convert::END_OFFSET => 3,
                ]
            ],
            [
                "''",
                "",
                0,
                'mysql',
                [
                    Convert::QUOTED =>  "''",
                    Convert::COL => '',
                    Convert::END_OFFSET => 1,
                ]
            ],
            [
                "'te''st'  ",
                "'",
                0,
                'mysql',
                [
                    Convert::QUOTED => "'te''st'",
                    Convert::COL => 'te\'st',
                    Convert::END_OFFSET => 7,
                ]
            ],
            [
                "'te\'st'  ",
                "'",
                0,
                'mysql',
                [
                    Convert::QUOTED => "'te\'st'",
                    Convert::COL => 'te\'st',
                    Convert::END_OFFSET => 7,
                ]
            ],
            [
                "`te\`st`  ",
                "`",
                0,
                'mysql',
                [
                    Convert::QUOTED => "`te\`st`",
                    Convert::COL => 'te`st',
                    Convert::END_OFFSET => 7,
                ]
            ],
            [
                "```\``  ",
                "`",
                0,
                'mysql',
                [
                    Convert::QUOTED => "```\``",
                    Convert::COL => '``',
                    Convert::END_OFFSET => 5,
                ]
            ],
            [
                '`c\\\\Slashes\n\rNL\tT`',
                "`",
                0,
                'mysql',
                [
                    Convert::QUOTED => '`c\\\\Slashes\n\rNL\tT`',
                    Convert::COL => "c\\Slashes\n\rNL\tT",
                    Convert::END_OFFSET => 20,
                ]
            ],
        ];
    }


    /**
     * @test
     * @dataProvider extractQuotProvider
     */
    public function extractQuotTest(string $sText,
                            string $sQuot,
                            int    $iStartOffset,
                            string $sDialect,
                            array  $aReturnExpected
    ): void
    {
        $aReturnActual = Convert::parseExtractQuotedValue($sText, $sQuot, $iStartOffset, $sDialect);
        $this->assertSame($aReturnExpected, $aReturnActual, print_r([$aReturnActual, func_get_args()], true));

    }


    public static function parseCreateTableProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [[
            "  /*
     *  #1075 - Incorrect table definition; there can be only one auto column and it must be defined as a key
     *  #3719 'utf8' is currently an alias for the character set UTF8MB3, but will be an alias for UTF8MB4 in a future release. Please consider using UTF8MB4 in order to be unambiguous.
     *  #1681 Integer display width is deprecated and will be removed in a future release.   ADICA INT fara paranteze. se merge pe auto */
  
  CREATE TABLE IF NOT EXISTS `fluentdb`.`muta#ble` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fkid` int(11) NOT NULL,
  `int_defa``ult'_none_unsigned` int(10) unsigned NOT NULL,
  `int_default_none_null` int(11) DEFAULT 0,
  `int_default_null_null` int(11) DEFAULT NULL,
  `1b_tinyint` tinyint(4) NOT NULL DEFAULT 22,
  `2b_smallint` smallint(6) NOT NULL,
  `3b_mediumint` mediumint(9) NOT NULL,
  `8b_bigint` bigint(20) NOT NULL,
  `decimalX` decimal( 10 ,2 ) NOT NULL,
  `floatX` float NOT NULL,
  double_floatX2 double NOT NULL,
  `date` date NOT NULL,
  `dt` datetime NOT NULL,
  `t` time NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `char_0_255_padded_with_spaces` char(2) NOT NULL,
  `varchar_0-65535` varchar(20) CHARACTER SET latin1 NOT NULL,
  `tinytxt_2_1` tinytext CHARACTER SET latin1 NOT NULL,
  `txt_2_2` text CHARACTER SET latin1 NOT NULL,
  `medtxt_2_3` mediumtext CHARACTER SET latin1 NOT NULL,
  `longtxt_2_4` longtext CHARACTER SET latin1 NOT NULL,
  `binary_as_chr_but_01` binary(4) NOT NULL,
  `varbinary_as_varchr_but_01` varbinary(6) NOT NULL,
  `tinyblob_2_1` tinyblob DEFAULT NULL COMMENT 'defau''lt tr\"ebui`e s)a fi(e null',
  `blob_2_16` blob DEFAULT NULL COMMENT 'default trebuie sa fie null',
  `medblob_2_24` mediumblob DEFAULT NULL COMMENT 'default trebuie sa fie null',
  `longblob_2_32` longblob DEFAULT NULL COMMENT 'default trebuie sa fie null',
  `enum_64k` enum( 'a','b' ,'c', '') CHARACTER SET latin1 NOT NULL,
  `set_max_64_vals` set('d','e','f','') CHARACTER SET latin1 NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `1b_tinyintx` (`1b_tinyint`,`t`),
  UNIQUE KEY `int_default_none_null` (`int_default_none_null`),
  KEY `fkmutk1` (`fkid`),
  KEY `fkmut1` (`int_defa``ult'_none_unsigned`),
  KEY `2b_smallint` (`2b_smallint`,`date`) USING BTREE,
  FULLTEXT KEY `txt_2_2` (`txt_2_2`),
#  CONSTRAINT `fkmut1` FOREIGN KEY (`int_defa``ult'_none_unsigned`) REFERENCES `article` (`id`) ON DELETE RESTRICT ON UPDATE NO ACTION
  CONSTRAINt `fkmut2` FOREIGN KEy (`int_defa``ult'_none_unsigned`, `8b_bigint`) REFERENCES `dbx`.article (`id`, `user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='yha-c\\\\om''ment!' 
     * */
"
            //ALTER TABLE `fluentdb`.`muta#ble` DROP INDEX `2b_smallint`, ADD INDEX `2b_smallint` (`2b_smallint`, `date`) USING BTREE;
        ]];
    }

    /**
     * @test
     * @dataProvider parseCreateTableProvider
     */
    public function parseCreateTableTest(string $sSQL): void
    {
        $this->assertSame('x', 'x'); return;

        ConvertSwitch::withDialect('mysql');
        $aReturnActual = ConvertSwitch::parseCreateTableBlueprint($sSQL);
        $aReturnActualQ = ConvertSwitch::blueprintToTableSql($aReturnActual);
        $aReturnActualQ = ConvertSwitch::parseCreatDatabaseBlueprint('');
        $p=AfrDbConnectionManager::getInstance()->dataLayerPath();
   //     $aReturnActual = '['.gettype('').'] ('.gettype(null).')';
        $this->assertSame('x', 'y', print_r([$p,$aReturnActualQ,$aReturnActual], true));

    }


    /**
     * @test
     */
    public function CnxActionFacadeTest(): void
    {
        $aResults = [];
        AfrDbConnectionManager::getInstance()->defineConnectionAlias(
            'test',
            "mysql:host=192.168.0.21",
            //"mysql:host=192.168.0.21;charset=utf8mb4",
            "git",
            "1234"
        );
        $oAfrDatabase = CnxActionFacade::withConnAlias('test');
//        $aResults['cnxGetAllDatabaseNames'] = $oAfrDatabase->cnxGetAllDatabaseNames();
//        $aResults['cnxGetAllDatabaseNames-%dmin%'] = $oAfrDatabase->cnxGetAllDatabaseNames('%dmin%');
        $aResults['cnxGetAllDatabaseNamesWithProperties'] = $oAfrDatabase->cnxGetAllDatabaseNamesWithProperties();
//        $aResults['cnxDatabaseExists-dms'] = $oAfrDatabase->cnxDatabaseExists('dms');
//        $aResults['cnxDatabaseExists-dmsX'] = $oAfrDatabase->cnxDatabaseExists('dmsX');
        $aResults['cnxDbGetDefaultCharsetAndCollation-admin_new'] = $oAfrDatabase->cnxDbGetDefaultCharsetAndCollation('admin_new');
        $aResults['charsetsX'] = AfrDbConnectionManager::getInstance()->getAliasInfo('test');
//        $aResults['cnxDbSetDefaultCharsetAndCollation-admin_new'] = $oAfrDatabase->cnxDbSetDefaultCharsetAndCollation('admin_new','utf8mb4');
//        $aResults['cnxCreateDatabaseUsingDefaultCharset'] = $oAfrDatabase->cnxCreateDatabaseUsingDefaultCharset('cnxCreateDatabaseUsingDefaultCharset'.time());
//        $aResults['cnxCreateDatabaseUsingCharset'] = $oAfrDatabase->cnxCreateDatabaseUsingCharset('cnxCreateDatabaseUsingCharset'.time());
//        $aResults['cnxGetAllCollationCharsets'] = $oAfrDatabase->cnxGetAllCollationCharsets();
//        $aResults['cnxGetAllCollationCharsets-utf8%general_ci'] = $oAfrDatabase->cnxGetAllCollationCharsets('utf8%general_ci',false);
        $aResults['cnxGetTimezone'] = $oAfrDatabase->cnxGetTimezone();


        $this->assertSame('x', 'y', print_r($aResults, true));

    }
}
