<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\AfrDbConnectionManagerFacade;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;

class DbActionFacade
{
	/**
	 * @param string $sConnAlias
	 * @param string $sDatabaseName
	 * @return DbActionInterface
	 * @throws AfrDatabaseConnectionException
	 */
	public static function withConnAliasAndDatabase(string $sConnAlias, string $sDatabaseName): DbActionInterface
	{
		/** @var DbActionInterface $sFQCN */
		$sFQCN = AfrDbConnectionManagerFacade::getInstance()->resolveFacadeUsingAlias(static::class, $sConnAlias);
		return $sFQCN::getInstanceWithConnAliasAndDatabase($sConnAlias, $sDatabaseName);
	}

	/**
	 * @param CnxActionInterface $oCnx
	 * @param string $sDatabaseName
	 * @return DbActionInterface
	 * @throws AfrDatabaseConnectionException
	 */
	public static function usingCnxiAndDatabase(CnxActionInterface $oCnx, string $sDatabaseName): DbActionInterface
	{
		return self::withConnAliasAndDatabase($oCnx->getNameConnAlias(), $sDatabaseName);
	}


}