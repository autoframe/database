<?php

namespace Autoframe\Database\Orm\Db;

use Autoframe\Database\Orm\Cnx\AfrOrmCnxInterface;

interface AfrOrmDbInterface extends AfrOrmCnxInterface
{
    /** @return string Database name. For Sqlite return empty string '' */
    public static function _ORM_Db_Name(): string;
}