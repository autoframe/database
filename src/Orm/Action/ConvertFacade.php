<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;
use Autoframe\Database\Orm\Exception\AfrOrmException;

class ConvertFacade implements AfrOrmBlueprintInterface
{
    use ResolveForFacade;
    /**
     * @param string $sAlias
     * @param string $sDialectClass
     * @return ConvertInterface
     * @throws AfrDatabaseConnectionException|AfrOrmException
     */
    public static function withConnAlias(string $sAlias, string $sDialectClass = ''): ConvertInterface
    {
        /** @var ConvertInterface $sFQCN_Implementing_ConvertInterface */
        $sFQCN_Implementing_ConvertInterface = static::resolveAlias($sAlias, $sDialectClass);
        return $sFQCN_Implementing_ConvertInterface::getInstanceWithConnAlias($sAlias);
    }
}