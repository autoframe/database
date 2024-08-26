<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;

trait With
{
    //define in each class where the trait is used the next properties:
    //protected static string $sDialectClass; //php 7.4-8 compat
    //protected static string $sAlias; //php 7.4-8 compat

    public static function withDialect(string $sDialect, string $sDialectClass = ''): string
    {
        if(empty($sDialectClass)) {
            $iSplit = (int)strrpos(static::class, '\\');
            $sNameSpace = substr(static::class, 0, $iSplit+1);
            $sClassToCall = substr(static::class, $iSplit+1);
            if(substr($sClassToCall,-6,6)==='Facade'){
                $sClassToCall = substr($sClassToCall,0,-6);// remove Facade from FQCN
            }
            //TODO de scos namespace de aici, si sa il scot din partile de explode
            //$sDialectClass = __NAMESPACE__ . '\\' . ucwords($sDialect) . '\\'.$sClassToCall;
            $sDialectClass = $sNameSpace . ucwords($sDialect) . '\\'.$sClassToCall;
        }


        return static::$sDialectClass = $sDialectClass;
    }

    /**
     * @param string $sAlias
     * @return string FQCN
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAlias(string $sAlias): string
    {
        static::$sAlias = $sAlias;

        return static::withDialect(
            AfrDbConnectionManager::getInstance()->driverType($sAlias)
        );
    }

}