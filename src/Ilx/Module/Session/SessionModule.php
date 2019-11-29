<?php


namespace Ilx\Module\Session;


use Ilx\Module\Database\DatabaseModule;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Session\Model\Session;
use Kodiak\Hook\SessionTokenHook;
use Kodiak\Session\Hook\PandabaseSessionHook;
use Kodiak\Session\Provider\SessionProvider;
use PandaBase\Connection\Scheme\Table;

class SessionModule extends IlxModule
{

    function defaultParameters()
    {
        return [];
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
        return [
            [
                "class_name" => SessionProvider::class,
                "parameters" => []
            ]
        ];
    }

    function hooks()
    {
        return [
            [
                "class_name" => PandabaseSessionHook::class,
                "parameters" => [
                    "connection_name" => "default",
                    "options" => [
                        "db_table" => "ilx_sessions"
                    ]
                ]
            ],
            [
                "class_name" => SessionTokenHook::class,
                "parameters" => []
            ],
        ];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        /** @var DatabaseModule $database_module */
        $database_module = $moduleManager::get("Database");

        $database_module->addTable(Session::class, [
            Table::TABLE_NAME => "ilx_sessions",
            Table::TABLE_ID   => "sess_id",
            Table::FIELDS     => [
                "sess_id"               => "varbinary(128) NOT NULL",
                "sess_data"             => "blob NOT NULL",
                "sess_time"             => "int(10) unsigned NOT NULL",
                "sess_lifetime"         => "mediumint(9) NOT NULL",
                "sess_ip"               => "varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL",
            ]
        ]);

    }

    function initScript($include_templates)
    {

    }
}