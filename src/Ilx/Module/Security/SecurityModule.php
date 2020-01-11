<?php


namespace Ilx\Module\Security;


use Basil\DataSource\ArraySource;
use Basil\DataSource\NestedSetSource;
use Basil\Tree;
use Ilx\Configuration;
use Ilx\Module\Database\DatabaseModule;
use Ilx\Module\Frame\FrameModule;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Security\Controller\AuthController;
use Ilx\Module\Security\Model\Auth\Remote\RemoteAuthenticationMode;
use Ilx\Module\Security\Model\Role;
use Ilx\Module\Security\Model\UserRole;
use InvalidArgumentException;
use Kodiak\Security\Hook\FirewallHook;
use Kodiak\Security\Hook\PandabaseAccessManagerHook;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\ServiceProvider\SecurityProvider\SecurityProvider;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Connection\Scheme\Table;

/**
 * Class SecurityModule
 *
 * auth_modes:
 * itt egy asszoc tömbben definiálhatjuk, hogy milyen auth_mode-kat lehet használni.
 * a kulcs a mód neve, value-ban pedig egy asszoc tömb van, ami a módhoz szükséges paramétereket tartalmazza.
 * pl.:
 *
 * ...
 * "auth_remote" => [
 *      "url" => "https://auth.myapplication.com/webservice/login"
 *      "http_method" => "POST",
 *      "token" => "e61e01a6a41747810001d52810e072441e061bba61e849109257d1c95ea1d8b4"
 * ],
 * ...
 *
 *
 * ha nem akarunk paraméter átadni, akkor is tartani kell formátumot:
 * pl:
 * "auth_remote" => []
 *
 * @package Ilx\Module\Security
 */
class SecurityModule extends IlxModule
{
    const AUTH_BASIC = "auth_basic";
    const AUTH_TWO_FACT = "auth_2fact";
    const AUTH_JWT = "auth_jwt";
    const AUTH_REMOTE = "auth_remote";


    function defaultParameters()
    {
        return [
            // Admin email cím amit a rendszer telepítésénél regisztrál be
            "admin" => null,
            // Használható authentikációs módok
            "auth_modes" => [
                self::AUTH_BASIC => []
            ],
            // A használható authentikációs módok közül melyiket kell használni
            "auth_selector" => "first",
            // Session lifetime
            "sess_exp_time" => 900,
            // jogosultságok a routekhoz
            "permissions" => []
        ];
    }

    function environmentalVariables()
    {
        return [];
    }

    function routes()
    {
        $routes = [
            "getAuthDialect" => [
                "method" => "POST",
                "url" => "/auth/dialect",
                "handler" => AuthController::class."::getAuthDialect"
            ],

            "renderLoginFrame" => [
                "method" => "GET",
                "url" => "/auth/login",
                "handler" => AuthController::class."::renderLogin"
            ],
        ];
        foreach ($this->parameters["auth_modes"] as $name => $params) {
            $auth_class_name =  SecurityModule::authModeDispatcher($name);
            /** @var AuthenticationMode $auth_mode */
            $auth_mode = new $auth_class_name($params);
            $routes = array_merge($routes, $auth_mode->routes());
        }
        return $routes;
    }

    function serviceProviders()
    {
        $auth_modes = [];
        foreach ($this->parameters["auth_modes"] as $name => $params) {
            $auth_modes[] = [
                "class_name" => SecurityModule::authModeDispatcher($name),
                "parameters" => $params
            ];
        }

        return [
            [
                "class_name" => SecurityProvider::class,
                "parameters" => [
                    "sess_exp_time" => $this->parameters["sess_exp_time"],
                    "auth_selector" => $this->parameters["auth_selector"],
                    "auth_modes"  => $auth_modes
                ]
            ]
        ];
    }

    function hooks()
    {
        $permissions = [
            "^\/auth\/dialect$" => [\Kodiak\Security\Model\User\Role::ANON_USER],
            "^\/auth\/login$" => [\Kodiak\Security\Model\User\Role::ANON_USER]
        ];
        foreach ($this->parameters["auth_modes"] as $name => $params) {
            $auth_class_name =  SecurityModule::authModeDispatcher($name);
            /** @var AuthenticationMode $auth_mode */
            $auth_mode = new $auth_class_name($params);
            $permissions = array_merge($permissions, $auth_mode->permissions());
        }
        // A végén hozzáfűzzük a paraméterként definiált permsissionoket
        $permissions = array_merge($permissions, $this->parameters["permissions"]);

        return [
            [
                [
                    "class_name" => FirewallHook::class,
                    "parameters" => [
                        "permissions" => $permissions
                    ]
                ],
                Configuration::NOT_ROUTER_HOOK
            ],
            [
                [
                    "class_name" => PandabaseAccessManagerHook::class,
                    "parameters" => []
                ],
                Configuration::NOT_ROUTER_HOOK
            ],
            [
                "\Kodiak\Security\Hook\SessionRouterHook::class",
                Configuration::ROUTER_HOOK
            ]

        ];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        print("Bootstraping SecurityModule...\n");
        /*
         * Ha létezik definiált FrameModule akkor hozzáadjuk a Security scripteket.
         */
        try {
            /** @var FrameModule $frame_module */
            $frame_module = $moduleManager::get("Frame");
            $frame_module->addTheme("security", ModuleManager::getInstance());
            print("\tAdded SecurityTheme to FrameModule.\n");
        }
        catch (InvalidArgumentException $exception) {
            print("\tAdding SecurityTheme has been skipped because the absence of FrameModule.\n");
        }


        /** @var DatabaseModule $database_module */
        $database_module = $moduleManager::get("Database");

        // A Role és UserRole mindig szerepel a rendszerben
        $tables = [
            Role::class  => [
                Table::TABLE_NAME => "roles",
                Table::TABLE_ID   => "node_id",
                Table::FIELDS     => [
                    "node_id"               => "int(11) unsigned NOT NULL AUTO_INCREMENT",
                    "node_lft"              => "int(11) DEFAULT NULL",
                    "node_rgt"              => "int(11) DEFAULT NULL",
                    "role_id"               => "int(11) unsigned NOT NULL",
                    "role_name"             => "varchar(255) DEFAULT NULL",
                    "role_desc"             => "varchar(255) DEFAULT NULL",
                ],
                Table::PRIMARY_KEY => ["node_id"]
            ],

            UserRole::class  => [
                Table::TABLE_NAME => "user_roles",
                Table::TABLE_ID   => "user_role_id",
                Table::FIELDS     => [
                    "user_role_id"          => "int(11) NOT NULL AUTO_INCREMENT",
                    "user_id"               => "int(11) unsigned NOT NULL",
                    "role_id"               => "int(11) unsigned NOT NULL",
                ],
                Table::PRIMARY_KEY => ["user_role_id"]
            ]
        ];

        // A meglévő user táblákat össze kell fésülni
        $user_classes = [];
        foreach ($this->parameters["auth_modes"] as $name => $params) {
            $auth_class_name = SecurityModule::authModeDispatcher($name);
            /** @var AuthenticationMode $auth_mode */
            $auth_mode = new $auth_class_name($params);
            $tables = array_merge($auth_mode->tables(), $tables);
            $user_classes[] = $auth_mode->userClass();
        }

        // Össze kell gyűjteni az összes lehetséges mezőt
        $user_fields = [];
        foreach ($user_classes as $user_class) {
            $user_fields = array_merge($user_fields, $tables[$user_class][Table::FIELDS]);
        }
        // Az univerzális user osztály legenerálása
        $univ_user_table = [
            Table::TABLE_NAME => "users",
            Table::TABLE_ID   => "user_id",
            Table::FIELDS     => $user_fields,
            Table::PRIMARY_KEY => ["user_id"]
        ];
        // Felülírjuk az eddigi létező tábla definíciókat
        foreach ($user_classes as $user_class) {
            $tables[$user_class] = $univ_user_table;
        }
        $database_module->addTables($tables);
    }

    function initScript($include_templates)
    {
        if($this->parameters["admin"] != null) {
            print("\tInserting admin user...\n");
            $admin_user = [
                "user_id"               => 1,
                "username"              => "sys_admin",
                "email"                 => $this->parameters["admin"],
                "firstname"             => "Gazda",
                "lastname"              => "Rendszer"
            ];
            $prepared_statement = ConnectionManager::getInstance()->getConnection()->prepare("
                INSERT INTO users (user_id, username, email, firstname, lastname) 
                VALUES (:user_id, :username, :email, :firstname, :lastname)");
            foreach ($admin_user as $key => $value) {
                $prepared_statement->bindValue($key, $value);
            }
            $prepared_statement->execute();
            ConnectionManager::getInstance()->getAccessManager()->registerUser(new User(1));
            print("\tAdmin user has been inserted.\n");


            print("\tInserting roles...\n");
            $roles = [
                "role_id"   => 99,
                "role_name" => "admin",
                "role_desc" => "Admin/Rendszergazda",
                "children"  => [
                    [
                        "role_id"   => 10,
                        "role_name" => "SuperUser",
                        "role_desc" => "Super user",
                    ]
                ]

            ];
            Tree::convert(new ArraySource([
                ArraySource::NODE_ID => "role_id",
                ArraySource::CHILDREN=> "children",
                ArraySource::ROOT_ID => 1
            ], $roles), new NestedSetSource([
                NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
                NestedSetSource::TABLE_NAME => "roles",
                NestedSetSource::NODE_ID    => "node_id",
                NestedSetSource::ROOT_ID    => 1,
                NestedSetSource::LEFT       => "node_lft",
                NestedSetSource::RIGHT      => "node_rgt"
            ]));
            print("\tRoles have been inserted.\n");


            print("\tAdding roles to users...\n");
            $user_roles = [
                [
                    "user_id"   => 1,
                    "role_id"   => 99
                ]
            ];
            foreach ($user_roles as $user_role) {
                $user_id = $user_role['user_id'];
                $role_id = $user_role['role_id'];
                UserRole::addUserTo($user_role["user_id"], $user_role["role_id"]);
                print("\tAdded role: $role_id to user: $user_id\n");
            }
            print("\tRoles has been added to users.\n");
        }
        else {
            print("\tMissing admin email ...\n");
        }
    }

    function addPermission($route_template, $groups) {
        $this->parameters["permissions"][$route_template] = $groups;
    }

    function addPermissions($permissions) {
        foreach ($permissions as $route_template => $groups) {
            $this->addPermission($route_template, $groups);
        }
    }

    private static function authModeDispatcher($name) {
        switch ($name) {
            case RemoteAuthenticationMode::name():
                return RemoteAuthenticationMode::class;
            default:
                throw new \InvalidArgumentException("Unknown authentication mode name: $name");
        }
    }
}