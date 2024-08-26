<?php

namespace Autoframe\Database\Orm\Tbl;

use Autoframe\Database\Orm\Cnx\AfrOrmCnxTrait;
use Autoframe\Database\Orm\Db\AfrOrmDbMutateTrait;
use Autoframe\Database\Orm\Db\AfrOrmDbTrait;
use Autoframe\Database\Orm\Ent\AfrOrmEntTrait;

/**
 * Base table model to be implemented or extended...
 * abstract class AfrOrmTblAbstractCompleteModel implements DB and HANDLERS
 * use AfrOrmTblTrait, AfrOrmEntTrait;
 */
abstract class AfrOrmTblAbstractCompleteModel implements AfrOrmTblInterface
{
    use AfrOrmTblTrait, AfrOrmEntTrait, AfrOrmCnxTrait, AfrOrmDbTrait, AfrOrmDbMutateTrait;
}