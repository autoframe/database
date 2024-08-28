<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

class CnxActionFacade implements AfrOrmBlueprintInterface
{
    use WithForFacade;

    /**
     * @param string $sAlias
     * @param string $sForceDialectClass
     * @return CnxActionInterface FQCN
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAlias(string $sAlias, string $sForceDialectClass = ''): CnxActionInterface
    {

        /** @var CnxActionInterface $sFQCN_Implementing_CnxActionInterface */
        $sFQCN_Implementing_CnxActionInterface = static::withDialect(
            AfrDbConnectionManager::getInstance()->driverType($sAlias),
            $sForceDialectClass
        );
        return $sFQCN_Implementing_CnxActionInterface::withConnAlias($sAlias);
    }

}