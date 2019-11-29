<?php


namespace Ilx\Module\Database;


use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Kodiak\ServiceProvider\PandabaseProvider\PandaBaseProvider;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\ConnectionNotExistsException;
use PDO;

class DatabaseModule extends IlxModule
{
    private $tables = [];

    function defaultParameters()
    {
        return [
            "name"      => "default_connection",
            "driver"    => "mysql",
            "dbname"    => "database_name",
            "host"      => "localhost",
            "user"      => "user",
            "password"  => "",
            "charset"   => "utf8",
            "attributes"=> [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
        ];
    }

    /**
     * Táblák hozzáadása.
     *
     * @param $tables
     */
    public function addTables($tables) {
        foreach ($tables as $class_name => $table) {
            $this->tables[$class_name] = $table;
        }
    }

    /**
     * Tábla hozzáadása
     *
     * @param array $table
     */
    public function addTable($class_name, $table) {
        $this->tables[$class_name] = $table;
    }

    function environmentalVariables()
    {
        return [];
    }

    function routes()
    {
        return [];
    }

    function serviceProviders()
    {
        $this->parameters["tables"] = $this->tables;
        return [
            [
                "class_name" => PandaBaseProvider::class,
                "parameters" => $this->parameters
            ]
        ];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {

    }

    /**
     * Létrehozza a táblákat az adatbázisban.
     *
     * @param bool $include_templates
     * @throws ConnectionNotExistsException
     */
    function initScript($include_templates)
    {
        print("Installing DatabaseModule...\n");
        ConnectionManager::getInstance()->initializeConnection($this->parameters);
        print("\tCreating database tables ...\n");
        $db = ConnectionManager::getInstance();
        $db->getDefault()->createTables();
        print("\tDatabase tables have been created.\n");
    }
}














