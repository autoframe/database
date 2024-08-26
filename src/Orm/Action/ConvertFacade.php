<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;
use Autoframe\Database\Orm\Exception\AfrOrmException;

class ConvertFacade implements ConvertInterface
{
    use With;
    protected static string $sDialectClass;
    protected static string $sAlias;

    /**
     * @throws AfrOrmException
     */
    public static function __callStatic($sMethod, $arguments)
    {
        if (empty(static::$sDialectClass)) {
            throw new AfrOrmException('Set dialect as follows: ' . __CLASS__ . '::withDialect( mysql | sqlite | ...)');
        }
        return forward_static_call_array([static::$sDialectClass, $sMethod], $arguments);
    }

    public static function blueprintToTableSql(array $aBlueprint): string
    {
        return static::__callStatic(__FUNCTION__, func_get_args());
    }

    public static function encapsulateDbTblColName(string $sDatabaseOrTableName, string $sQuot = ''): string
    {
        return static::__callStatic(__FUNCTION__, func_get_args());
    }

    public static function encapsulateCellValue($sCellValue)
    {
        return static::__callStatic(__FUNCTION__, func_get_args());
    }

    public static function parseExtractQuotedValue(string $sText, string $sQuot, int $iStartOffset = 0): array
    {
        return static::__callStatic(__FUNCTION__, func_get_args());
    }

    public static function parseCreateTableBlueprint(string $sTableSql): array
    {
        return static::__callStatic(__FUNCTION__, func_get_args());
    }
}