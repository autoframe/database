<?php

namespace Autoframe\Database\Cache;

use Autoframe\Database\Orm\Action\CnxActionInterface;
use Autoframe\Database\Orm\Action\DbActionInterface;
use Autoframe\Database\Orm\Action\TblActionInterface;
use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\DesignPatterns\Singleton\AfrSingletonInterface;
use Closure;

interface AfrDbStructureCacheInterface // extends AfrSingletonInterface
{

	//TODO EVENT TRIGGERING!!!

	/**
	 * Creates a cache key in the connection index.
	 * This may be used for checking database or table existence or table description on runtime
	 * @param AfrDbCacheKey $oKey
	 * @param Closure|null $closureSet
	 * @return mixed
	 * @throws AfrDatabaseConnectionException
	 */
	public function cacheStructureGet(AfrDbCacheKey $oKey, ?Closure $closureSet = null);

	/**
	 * @param AfrDbCacheKey $oKey
	 * @param $mValue
	 * @throws AfrDatabaseConnectionException
	 */
	public function cacheStructureSet(AfrDbCacheKey $oKey, $mValue);


	/**
	 * @param string $sFunction
	 * @param array $aParams
	 * @param string|null $sCnxAlias
	 * @param string|null $sDbName
	 * @param string|null $sTableName
	 * @return AfrDbCacheKey
	 * @throws AfrDatabaseConnectionException
	 */
	public function cacheKey(
		string $sFunction,
		array  $aParams,
		string $sCnxAlias = null,
		string $sDbName = null,
		string $sTableName = null
	): AfrDbCacheKey;


	/**
	 * Flushes the cached keys form all connections
	 */
	public function cacheFlushAll(): void;

	/**
	 * Flushes the cached key form the connexion alias
	 * @param string $sAlias
	 * @return void
	 */
	public function cacheFlushAlias(string $sAlias): void;

	public function cacheFlushAliasDb(string $sAlias, string $sDatabaseName): void;

	public function cacheFlushAliasDbTable(string $sAlias, string $sDatabaseName, string $sTableName): void;


	//public static function getInstance():self;

}