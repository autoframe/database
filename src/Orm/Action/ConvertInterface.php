<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;
use Autoframe\Database\Orm\Exception\AfrOrmException;

interface ConvertInterface extends AfrOrmBlueprintInterface #, CnxActionSingletonInterface
{
// todo: de descompus / mutat din AfrOrmActionInterface \ use Doctrine\DBAL\Types\Types;

    /**
     * @throws AfrOrmException
     */
    public static function blueprintToTableSql(array $aBlueprint): string;

    public static function encapsulateDbTblColName(string $sDatabaseOrTableName): string;

    public static function encapsulateCellValue($mData);

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