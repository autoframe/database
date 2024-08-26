<?php

namespace Autoframe\Database\Orm\Action\Mysql;


trait Encapsulate
{
    public static function encapsulateDbTblColName(string $sDatabaseOrTableName, string $sQuot = ''): string
    {
        $sQuot = !empty($sQuot) ? $sQuot : static::$__sTildeQuot;
        return $sQuot .
            str_replace(
                [$sQuot, static::$__sBackSlash],
                [$sQuot . $sQuot, static::$__sBackSlash . static::$__sBackSlash],
                $sDatabaseOrTableName,
            )
            . $sQuot;
    }


    public static function encapsulateCellValue($sCellValue)
    {
        //todo ints, floats, NULL as null, etc
        if ($sCellValue === null) {
            return 'NULL';
        } elseif (is_integer($sCellValue) || is_float($sCellValue)) {
            return $sCellValue;
        } elseif (is_bool($sCellValue)) {
            return $sCellValue ? 1 : 0;
        } elseif (is_array($sCellValue)) { //todo: enum/set INSERT INTO `t` (`set_max_64_vals`) VALUES ( 'a\'\"b,x`x');
            $sCellValue = implode(',', str_replace(',', ';', $sCellValue)); //#1367 - Illegal set 'comma,comma' value found during parsing
        } elseif (is_object($sCellValue)) {
            $sCellValue = json_encode($sCellValue);
        }

        return static::$__sSingleQuot .
            str_replace(
                [static::$__sSingleQuot, static::$__sBackSlash],
                [static::$__sSingleQuot . static::$__sSingleQuot, static::$__sBackSlash . static::$__sBackSlash],
                (string)$sCellValue,
            )
            . static::$__sSingleQuot;

    }
}