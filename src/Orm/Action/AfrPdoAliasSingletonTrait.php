<?php

namespace Autoframe\Database\Orm\Action;

use \Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;

trait AfrPdoAliasSingletonTrait
{
    /**
     * The actual singleton's instance almost always resides inside a static
     * field. In this case, the static field is an array, where each subclass of
     * the Singleton stores its own instance.
     */
    protected static array $instances = [];
    protected string $sConnAlias;

    public function getConnAlias(): string
    {
        return $this->sConnAlias;
    }

    /**
     * Singleton's constructor should not be public. However, it can't be
     * private either if we want to allow subclassing.
     */
    final protected function __construct(string $sConnAlias)
    {
        $this->sConnAlias = $sConnAlias;

    }

    /**
     * Cloning and un-serialization are not permitted for singletons.
     * @throws AfrDatabaseConnectionException
     */
    final public function __clone()
    {
        throw new AfrDatabaseConnectionException('Cannot clone a singleton: ' . static::class);
    }

    /**
     * @throws AfrDatabaseConnectionException
     */
    final public function __wakeup()
    {
        throw new AfrDatabaseConnectionException('Cannot unserialize singleton: ' . static::class);
    }


    /**
     * The method you use to get the Singleton's instance.
     * @return self
     * @throws AfrDatabaseConnectionException
     */
    final public static function makeFromConnAlias(string $sConnAlias): self
    {
        if (empty($sConnAlias)) {
            throw new AfrDatabaseConnectionException('Alias can\'t be empty');
        }
        if (empty(self::$instances[static::class][$sConnAlias])) {
            return self::$instances[static::class][$sConnAlias] = new static($sConnAlias);
        }
        return self::$instances[static::class][$sConnAlias];
    }


}