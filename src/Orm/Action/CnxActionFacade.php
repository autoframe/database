<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Components\Exception\AfrException;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

class CnxActionFacade implements AfrOrmBlueprintInterface
{
    use ResolveForFacade;

    /**
     * @param string $sAlias
     * @param string $sDialectClass
     * @return CnxActionInterface
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAlias(string $sAlias, string $sDialectClass = ''): CnxActionInterface
    {
        /** @var CnxActionInterface $sFQCN_Implementing_CnxActionInterface */
        $sFQCN_Implementing_CnxActionInterface = static::resolveAlias($sAlias, $sDialectClass);
        return $sFQCN_Implementing_CnxActionInterface::makeFromConnAlias($sAlias);
    }

}