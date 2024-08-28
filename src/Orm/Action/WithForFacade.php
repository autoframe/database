<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;

trait WithForFacade
{
    /**
     * @param string $sDialect Types: mysql, sqlite, pgsql, mssql, cubrid, sybase, dblib, firebird, ibm, informix, oci, odbc
     * @param string $sDialectClass The fully qualified class name for the database dialect.
     * @return string FQCN
     */
    public static function withDialect(string $sDialect, string $sDialectClass = ''): string
    {
        if (empty($sDialectClass)) {
            $iSplit = (int)strrpos(static::class, '\\');
            $sClassToCall = substr(static::class, $iSplit + 1);
            if (substr($sClassToCall, -6, 6) === 'Facade') {
                $sClassToCall = substr($sClassToCall, 0, -6);// remove Facade from FQCN
            }
            $sDialectClass =
                substr(static::class, 0, $iSplit + 1) . // __NAMESPACE__\
                ucwords($sDialect) . '\\' . $sClassToCall;
        }
        return $sDialectClass;
    }

    /**
     * Returns the fully qualified class name for a given dialect alias.
     *
     * @param string $sAlias The connection alias.
     * @return string The fully qualified class name for the database dialect.
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAlias(string $sAlias, string $sDialectClass = ''): string
    {
        return static::withDialect(
            AfrDbConnectionManager::getInstance()->driverType($sAlias),
            $sDialectClass
        );
    }

}