<?php

namespace Autoframe\Database\Orm\Migrate;

use Autoframe\Database\Connection\AfrDbConnectionManager;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Action\ConvertFacade as ConvertSwitch;
use Autoframe\Database\Orm\Action\DbActionFacade;

class MigrateFromDb
{
    /**
     * @param string $sAlias
     * @return void
     * @throws AfrDatabaseConnectionException
     */
    public static function migrateAllDbsFromAlias(string $sAlias){
        $oManager = AfrDbConnectionManager::getInstance();
        $oAfrDatabase = DbActionFacade::withConnAlias($sAlias);
        $dbListAllWithProperties = $oAfrDatabase->dbListAllWithProperties();
        //  [db...]
        //  [dms_new] => Array
        //                (
        //                    [CATALOG_NAME] => def
        //                    [SCHEMA_NAME] => dms_new
        //                    [DEFAULT_CHARACTER_SET_NAME] => utf8
        //                    [DEFAULT_COLLATION_NAME] => utf8_general_ci
        //                    [SQL_PATH] =>
        //                )
        ConvertSwitch::withConnAlias($sAlias);

        $oPdo = $oManager->getConnectionByAlias($sAlias);

        //TODO: list all DBS
    }
}