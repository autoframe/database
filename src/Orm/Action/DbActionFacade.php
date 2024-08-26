<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

class DbActionFacade implements AfrOrmBlueprintInterface
{
    use With;
    protected static string $sDialectClass; //php 7.4-8 compat
    protected static string $sAlias; //php 7.4-8 compat

    /**
     * @param string $sAlias
     * @return DbActionInterface FQCN
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAlias(string $sAlias, string $sForceDialectClass = ''): DbActionInterface
    {
        static::$sAlias = $sAlias;

        /** @var DbActionInterface $sFQCN_Implementing_DbActionInterface */
        $sFQCN_Implementing_DbActionInterface = static::withDialect(
            AfrDbConnectionManager::getInstance()->driverType($sAlias),
            $sForceDialectClass
        );
        return $sFQCN_Implementing_DbActionInterface::withConnAlias($sAlias);
    }

}