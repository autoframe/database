<?php

namespace Autoframe\Database\Orm\Action;

trait RowHelperTrait
{

    public static function rowToArray($objOrArr): array
    {
        return is_object($objOrArr) ? get_object_vars($objOrArr) : (array)$objOrArr;
    }

    public static function rowsToArray($traversable): array
    {
        $aOut = [];
        if(!empty($traversable)){
            foreach ($traversable as $k => $mData) {
                $aOut[$k] = static::rowToArray($mData);
            }
        }

        return $aOut;
    }

    public static function rowToStdClass($objOrArr): object
    {
        return is_object($objOrArr) ? $objOrArr : (object)$objOrArr;
    }

    public static function rowsToStdClass($traversable, bool $bReference = true)
    {
        if ($bReference) {
            foreach ($traversable as &$mData) {
                $mData = static::rowToStdClass($mData);
            }
        } else {
            foreach ($traversable as $k => $mData) {
                $traversable[$k] = static::rowToStdClass($mData);
            }
        }

        return $traversable;
    }

}