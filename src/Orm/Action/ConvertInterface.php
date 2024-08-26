<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;
use Autoframe\Database\Orm\Exception\AfrOrmException;

interface ConvertInterface extends AfrOrmBlueprintInterface
{

    /**
     * @throws AfrOrmException
     */
    public static function blueprintToTableSql(array $aBlueprint): string;

    public static function encapsulateDbTblColName(string $sDatabaseOrTableName, string $sQuot = ''): string;

    public static function encapsulateCellValue($sCellValue);

    /**
     * @param string $sText
     * @param string $sQuot
     * @param int $iStartOffset
     * @return string[]
     * @throws AfrOrmException
     */
    public static function parseExtractQuotedValue(
        string $sText,
        string $sQuot,
        int    $iStartOffset = 0
    ): array;

    public static function parseCreateTableBlueprint(string $sTableSql): array;

}