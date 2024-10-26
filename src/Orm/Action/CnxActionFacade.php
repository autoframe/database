<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManagerFacade;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;

class CnxActionFacade
{
	/**
	 * @param string $sConnAlias
	 * @return CnxActionInterface
	 * @throws AfrDatabaseConnectionException
	 */
	public static function withConnAlias(string $sConnAlias): CnxActionInterface
	{
		/** @var CnxActionInterface $sFQCN */
		$sFQCN = AfrDbConnectionManagerFacade::getInstance()->resolveFacadeUsingAlias(static::class, $sConnAlias);
		return $sFQCN::getInstanceWithConnAlias($sConnAlias);
	}

}