<?php

namespace Autoframe\Database\Orm\Tbl;

use Autoframe\Database\Orm\Db\AfrOrmDbAbstractModel;
use Autoframe\Database\Orm\Ent\AfrOrmEntTrait;

/**
 * Base table model to be implemented or extended...
 * abstract class AfrOrmTblAbstractModel extends AfrOrmDbAbstractModel implements AfrOrmTblInterface
 * use AfrOrmTblTrait, AfrOrmEntTrait;
 */
abstract class AfrOrmTblAbstractModel extends AfrOrmDbAbstractModel implements AfrOrmTblInterface
{
    use AfrOrmTblTrait, AfrOrmEntTrait;
}