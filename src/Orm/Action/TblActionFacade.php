<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;

class TblActionFacade
{
    /**
     * @param string $sConnAlias
     * @param string $sDatabaseName
     * @param string $sTableName
     * @return TblActionInterface
     * @throws AfrDatabaseConnectionException
     */
    public static function withConnAliasAndDatabaseAndTable(
        string $sConnAlias,
        string $sDatabaseName,
        string $sTableName
    ): TblActionInterface
    {
        /** @var TblActionInterface $sFQCN */
        $sFQCN = AfrDbConnectionManager::getInstance()->resolveFacadeUsingAlias(static::class, $sConnAlias);
        return $sFQCN::getInstanceWithConnAliasAndDatabaseAndTable($sConnAlias, $sDatabaseName, $sTableName);
    }

}