<?php

namespace Autoframe\Database\Orm\Tbl;

use Autoframe\Database\Orm\Db\AfrOrmDbInterface;
use Autoframe\Database\Orm\Ent\AfrOrmEntInterface;

interface AfrOrmTblInterface extends AfrOrmDbInterface,AfrOrmEntInterface
{
    /** @return string Table name */
    public static function _ORM_Tbl_Name(): string;
}