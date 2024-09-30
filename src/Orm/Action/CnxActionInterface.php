<?php

namespace Autoframe\Database\Orm\Action;

use Autoframe\Database\Connection\Exception\AfrDatabaseConnectionException;
use Autoframe\Database\Orm\Blueprint\AfrOrmBlueprintInterface;

interface CnxActionInterface extends AfrOrmBlueprintInterface, EscapeInterface
{

    /**
     * @param string $sAlias
     * @return self
     * @throws AfrDatabaseConnectionException
     */
    public static function getInstanceWithConnAlias(string $sAlias): self;

    /**
     * @return string
     * @throws AfrDatabaseConnectionException
     */
    public function getNameDriver(): string; //from cnx manager
    public function getNameConnAlias(): string; //singleton info

    /**
     * @param string $sDbNameLike filter database name like or %startsWith or containing %part%
     * @return array
     */
    public function cnxGetAllDatabaseNames(string $sDbNameLike = ''): array;

    /**
     * The response array should contain the keys: self::DB_NAME, self::CHARSET, self::COLLATION
     * @param string $sDbNameLike
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function cnxGetAllDatabaseNamesWithCharset(string $sDbNameLike = ''): array;


    public function cnxDatabaseExists(string $sDbName): bool;

    public function cnxDbGetCharsetAndCollation(string $sDbName): array;

    public function cnxDbSetCharsetAndCollation(string $sDbName, string $sCharset, string $sCollation = ''): bool;


    public function cnxCreateDatabaseUsingDefaultCharset(string $sDbName, array $aOptions = [], bool $bIfNotExists = false): bool;

    public function cnxCreateDatabaseUsingCharset(
        string $sDbName,
        string $sCharset = 'utf8mb4',           //todo: _900_ai_ci  compatibility
        string $sCollate = 'utf8mb4_general_ci', //todo: _900_ai_ci
        array  $aOptions = [],
        bool   $bIfNotExists = false
    ): bool;


    /**
     * @param string $sLike
     * @param bool $bWildcard
     * @return array
     * @throws AfrDatabaseConnectionException
     */
    public function cnxGetAllCollationCharsets(string $sLike = '', bool $bWildcard = false): array;


    /**
     * Retrieves all available character sets from the database.
     *
     * @return array An array of character set names.
     * @throws AfrDatabaseConnectionException If there is an error connecting to the database.
     */
    public function cnxGetAllCharsets(): array;  //SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC;

    /**
     * Retrieves all the collations from the database.
     *
     * @return array An array containing all the collations.
     * @throws AfrDatabaseConnectionException If there is an issue with the database connection.
     */
    public function cnxGetAllCollations(): array; //SHOW COLLATION     //SELECT * FROM `information_schema`.`CHARACTER_SETS` ORDER BY `CHARACTER_SETS`.`CHARACTER_SET_NAME` DESC;

    public function cnxSetConnectionCharsetAndCollation(string $sCharset = 'utf8mb4',
                                                        string $sCollation = 'utf8mb4_general_ci',
                                                        bool   $character_set_server = true,
                                                        bool   $character_set_database = false
    ): bool;

    /**
     * @param string $sDbName
     * @return string
     */
    public function cnxShowCreateDatabase(string $sDbName): string;

    //https://stackoverflow.com/questions/2934258/how-do-i-get-the-current-time-zone-of-mysql
    //https://phoenixnap.com/kb/change-mysql-time-zone
    //https://www.db4free.net/

    public function cnxSetTimezone(string $sTimezone = '+00:00'): bool;

    public function cnxGetTimezone(): string; //'+00:00';

}