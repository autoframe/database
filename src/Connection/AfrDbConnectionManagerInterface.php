<?php

namespace Autoframe\Database\Connection;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Closure;
use PDO;

interface AfrDbConnectionManagerInterface
{
    const PDO_ARGS = 'aPdoArgs';
    const DRIVER = 'sDriver';
    const PDO_INSTANCE_KEY = 'sPDOInstanceKey';
    const CLOSURE = 'oClosure';
    const INFO = 'aInfo';
    const FQCN_PDO = 'sPdoFqcnClass';
    const CUSTOM_DIALECT_CNX_NS = 'sDialectOrmActionNamespace';

    const DSN = 'sDSN';
    const HOST = 'host';
    const PORT = 'port';
    const DBNAME = 'dbname';
    const CHARSET = 'sCharset';

    const DRIVERS_PORTS = [
        'cubrid' => 8001,
        'sybase' => 2638,
        'mssql' => 1433,
        'dblib' => 1433,
        'firebird' => 3050,
        'ibm' => 56789,
        'informix' => 9800,
        'mysql' => 3306,
        'oci' => 1521,
        'odbc' => 50000,
        'pgsql' => 5432,
        'sqlite' => null,
    ];


    /**
     * @param string|null $sDataLayerNamespace
     * @return string or default namespace: Autoframe\DataLayer\
     */
    public function dataLayerNamespace(string $sDataLayerNamespace = null): string;

    /**
     * @param string|null $sDataLayerPath
     * @return string
     * @throws AfrDatabaseConnectionException
     */
    public function dataLayerPath(string $sDataLayerPath = null): string;


    /**
     * @param string $sAlias
     * @param string $sDSN
     * @param string|null $username
     * @param string|null $password
     * @param array|null $options
     * @return AfrDbConnectionManagerInterface
     * @throws AfrDatabaseConnectionException
     */
    public function defineConnectionAlias(string $sAlias, string $sDSN, ?string $username = null, ?string $password = null, ?array $options = null): AfrDbConnectionManagerInterface;


    /**
     * @param string $sAlias
     * @param PDO $pdo
     * @param string $sDriver Types: mysql, sqlite, pgsql, mssql, cubrid, sybase, dblib, firebird, ibm, informix, oci, odbc
     * @param string $sDialectOrmActionNamespace
     * @return void
     * @throws AfrDatabaseConnectionException
     */
    public function defineConnectionAliasUsingPDOInstance(string $sAlias, PDO $pdo, string $sDriver, string $sDialectOrmActionNamespace = ''): AfrDbConnectionManagerInterface;


    /**
     * @param object $obj
     * @return string
     */
    public function pdoToHash(object $obj): string;

    /**
     * @param string $sAlias
     * @param Closure $oClosure
     * @return $this
     * @throws AfrDatabaseConnectionException
     */
    public function defineAliasClosure(string $sAlias, Closure $oClosure): AfrDbConnectionManagerInterface;


    /**
     * @param string $sAlias
     * @param string $sDialectOrmActionNamespace
     * @return $this
     * @throws AfrDatabaseConnectionException
     */
    public function defineCustomDialectCnxNs(
        string  $sAlias,
        string $sDialectOrmActionNamespace
    ): AfrDbConnectionManagerInterface;
    /**
     * @param $sAlias
     * @return PDO
     * @throws AfrDatabaseConnectionException
     */
    public function getConnectionByAlias($sAlias): PDO;

    /**
     * @param $sAlias
     * @return array|null
     */
    public function getAliasInfo($sAlias): ?array;

    /**
     * @param string $sAlias
     * @return bool
     */
    public function isConnected(string $sAlias): bool;


    /**
     * @param string $sAlias
     * @return string Types: mysql, sqlite, pgsql, mssql, cubrid, sybase, dblib, firebird, ibm, informix, oci, odbc
     * @throws AfrDatabaseConnectionException
     */
    public function getDriverType(string $sAlias): string;


    /**
     * @param string $sAlias
     * @return string
     * @throws AfrDatabaseConnectionException
     */
    public function getCustomDialectCnxNs(string $sAlias): string;

    /**
     * @return void
     * @throws AfrDatabaseConnectionException
     */
    public function connectToAll(): void;


    /** Sets connection index to null */
    public function flushConnection(string $sAlias): void;

    /**
     * The method you use to get the Singleton's instance.
     * @return AfrDbConnectionManagerInterface
     */
    //public static function getInstance(): AfrDbConnectionManagerInterface;
}