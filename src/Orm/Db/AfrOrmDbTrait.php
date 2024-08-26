<?php

namespace Autoframe\Database\Orm\Db;

use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;
use Autoframe\Database\Orm\Exception\AfrOrmException;

trait AfrOrmDbTrait
{
    /**
     * @return string Database name. For Sqlite return empty string ''
     * @throws AfrOrmException
     */
    public static function _ORM_Db_Name(): string
    {
        //todo load from blueprint or:
        if(isset(static::$aDBBlueprint) && !empty(static::$aDBBlueprint[AfrOrmBlueprintInterface::DB_NAME])){
            //            AfrDbBlueprint::dbBlueprint();
            return static::$aDBBlueprint[AfrOrmBlueprintInterface::DB_NAME];
        }
        if (rand(1, 2)) { //prevent code sniffer unreachable statement
            throw new AfrOrmException(
                'Please define a method for database name inside class [' .
                static::class . '] as follows: public static function _ORM_Db_Name(): string; ' .
                'For Sqlite return empty string'
            );
        }
        return '';
    }
}