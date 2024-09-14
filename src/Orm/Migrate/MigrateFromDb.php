<?php

namespace Autoframe\Database\Orm\Migrate;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\ConvertFacade as ConvertSwitch;
use Autoframe\Database\Orm\Action\CnxActionFacade;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

class MigrateFromDb implements AfrOrmBlueprintInterface
{
    /**
     * @param string $sAlias
     * @return void
     * @throws AfrDatabaseConnectionException
     */
    public static function migrateAllDbsFromAlias(string $sAlias)
    {
        $oAfrDatabase = CnxActionFacade::withConnAlias($sAlias);
        //The response array should contain the keys: self::DB_NAME, self::CHARSET, self::COLLATION
        foreach ($oAfrDatabase->cnxGetAllDatabaseNamesWithProperties() as $dbProperties) {
            $sCharset = $dbProperties[self::CHARSET];
            $sCollation = $dbProperties[self::COLLATION];
            $sDbName = $dbProperties[self::DB_NAME];
            if (in_array($sDbName, ['information_schema', 'performance_schema'])) {
                continue;
            }
            ConvertSwitch::withConnAlias($sAlias);

        }

    }
}