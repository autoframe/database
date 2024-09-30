<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;
use Autoframe\Database\Orm\Exception\AfrOrmException;

class ConvertFacade implements AfrOrmBlueprintInterface
{
    /**
     * @param string $sAlias
     * @return ConvertInterface
     * @throws AfrDatabaseConnectionException|AfrOrmException
     */
    public static function withConnAlias(string $sAlias): ConvertInterface
    {
        /** @var ConvertInterface $sFQCN */
        $sFQCN = AfrDbConnectionManager::getInstance()->resolveFacadeUsingAlias(static::class, $sAlias);
        return $sFQCN::getInstanceWithConnAlias($sAlias);
    }
}