<?php

namespace Autoframe\Database\Cache;

class AfrDbStructureCacheFacade
{
	protected static ?AfrDbStructureCacheInterface $oInstance = null;

	/**
	 * @var string|null FQCN class name that implements AfrDbStructureCacheInterface
	 */
	protected static ?string $sFQCN_Implementation = null;
	protected static ?string $sFQCN_That_Extends_AfrDbCacheKey = null;

	public static function getInstance(): AfrDbStructureCacheInterface
	{
		if (!empty(static::$sFQCN_Implementation)) {
			/** @var AfrDbStructureCacheInterface $sFQCN_Implementation */
			$sFQCN_Implementation = static::$sFQCN_Implementation;
			static::$sFQCN_Implementation = null;
			return static::setInstance($sFQCN_Implementation::getInstance());
		}
		if (!empty(static::$oInstance)) {
			return static::$oInstance;
		}
		if (!empty($_ENV['AfrDbStructureCacheFacade'])) { //TODO env!!!
			/** @var AfrDbStructureCacheInterface $sFQCN_Implementation */
			$sFQCN_Implementation = $_ENV['AfrDbConnectionManagerFacade'];
			$_ENV['AfrDbStructureCacheFacade'] = null;
			return static::setInstance($sFQCN_Implementation::getInstance());
		}

		return AfrDbStructureCacheRam::getInstance();
	}

	public static function setInstance(AfrDbStructureCacheInterface $oInstance): AfrDbStructureCacheInterface
	{
		static::$sFQCN_Implementation = null;
		return self::$oInstance = $oInstance;
	}

	public static function setFQCN(string $sFQCN_Implementation): void
	{
		static::$sFQCN_Implementation = $sFQCN_Implementation;
		self::$oInstance = null;
	}

	/**
	 * @param string $sFQCN_That_Extends_AfrDbCacheKey
	 * @return void
	 */
	public static function setAfrDbCacheKey_FQCN(string $sFQCN_That_Extends_AfrDbCacheKey): void
	{
		static::$sFQCN_That_Extends_AfrDbCacheKey = $sFQCN_That_Extends_AfrDbCacheKey;
	}

	public static function getAfrDbCacheKey_FQCN(): string
	{
		return static::$sFQCN_That_Extends_AfrDbCacheKey ?? AfrDbCacheKey::class;
	}


}