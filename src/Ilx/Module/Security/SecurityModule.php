<?php


namespace Ilx\Module\Security;


use Basil\DataSource\ArraySource;
use Basil\DataSource\NestedSetSource;
use Basil\Tree;
use Ilx\Module\Database\DatabaseModule;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Security\Model\PasswordHistory;
use Ilx\Module\Security\Model\Role;
use Ilx\Module\Security\Model\User;
use Ilx\Module\Security\Model\UserRole;
use Kodiak\Security\Hook\FirewallHook;
use Kodiak\Security\Hook\PandabaseAccessManagerHook;
use Kodiak\Security\Model\User\Role as KodiakRole;
use Kodiak\Security\PandabaseAuthentication\PAv1Authentication;
use Kodiak\Security\PandabaseAuthentication\PAv2Authentication;
use Kodiak\ServiceProvider\SecurityProvider\SecurityProvider;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Connection\Scheme\Table;

class SecurityModule extends IlxModule
{
    const TYPE_PASSWORD = "auth_pwd";
    const TYPE_TWO_FACTOR = "auth_2fact";
    const TYPE_JWT = "auth_jwt";

    // TODO: A döntés az lett, hogy első körben egyszerre csak egy módon lehet authentikálni a három közül
    // permission megadása legyen lehetséges
    // legyen mindegyik auth típushoz külön url lista. egyedi nevekkel
    // legyen egy auth/dialect, ami az aktuális dialektust adja vissza

    function defaultParameters()
    {
        return [
            "type" => self::TYPE_PASSWORD,
            "expiration_time" => 900,
            "registration" => true,
            "admin" => "admin@ilx.hu",
            "permissions" => [],
            "views" => []
        ];
    }

    function environmentalVariables()
    {
        return [
            "auth_dialect" => $this->parameters["type"]
        ];
    }

    function routes()
    {
        // TODO: csak ahhoz a renderhez adunk route-t amihez létezik view
        $routes = [

        ];
    }

    function serviceProviders()
    {
        $types = [
            SecurityModule::TYPE_PASSWORD => [
                "class_name" => PAv1Authentication::class,
                "parameters" => []
            ],
            SecurityModule::TYPE_TWO_FACTOR => [
                "class_name" => PAv2Authentication::class,
                "parameters" => []
            ]
        ];


        return [
            [
                "class_name" => SecurityProvider::class,
                "parameters" => [
                    "expiration_time" => 900,
                    "user_class_name" => User::class,
                    "authentication"  => [$types[$this->parameters["type"]]],
                    "admin_email"  => $this->parameters["admin"]
                ]
            ]
        ];
    }

    function hooks()
    {
        return [
            [
                "class_name" => FirewallHook::class,
                "parameters" => [
                    "permissions" => [
                        "^\/user\/login$" => [KodiakRole::ANON_USER, KodiakRole::PENDING_USER],
                        "^\/user\/auth\/dialect$" => [KodiakRole::ANON_USER, KodiakRole::PENDING_USER],
                        "^\/user\/welcome" => [KodiakRole::ANON_USER],
                        "^\/user\/requestpasswordreset$" => [KodiakRole::ANON_USER],
                        "^\/user\/changepass$" => [KodiakRole::ANON_USER],
                        "^\/user\/resetpassword" => [KodiakRole::ANON_USER],
                        "^.+" => [KodiakRole::AUTH_USER]
                    ]
                ]
            ],
            [
                "class_name" => PandabaseAccessManagerHook::class,
                "parameters" => []
            ],
        ];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        /** @var DatabaseModule $database_module */
        $database_module = $moduleManager::get("Database");

        $database_module->addTables([
            User::class  => [
                Table::TABLE_NAME => "cp_users",
                Table::TABLE_ID   => "user_id",
                Table::FIELDS     => [
                    "user_id"               => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "username"              => "varchar(200) DEFAULT NULL",
                    "email"                 => "varchar(200) NOT NULL",
                    "name_prefix"           => "varchar(20) DEFAULT NULL",
                    "firstname"             => "varchar(256) DEFAULT NULL",
                    "lastname"              => "varchar(256) DEFAULT NULL",
                    "status_id"             => "int(1) DEFAULT NULL",
                    "password"              => "varchar(256) DEFAULT NULL",
                    "password_expire"       => "datetime DEFAULT NULL",
                    "mfa_secret"            => "varchar(50) DEFAULT NULL",
                    "last_login"            => "datetime DEFAULT NULL ",
                    "reset_token"           => "varchar(200) DEFAULT NULL",
                    "failed_login_count"    => "int(10) NOT NULL DEFAULT '0'"

                ],
                Table::PRIMARY_KEY => ["user_id"]
            ],

            Role::class  => [
                Table::TABLE_NAME => "cp_roles",
                Table::TABLE_ID   => "node_id",
                Table::FIELDS     => [
                    "node_id"               => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "node_lft"              => "int(10) DEFAULT NULL",
                    "node_rgt"              => "int(10) DEFAULT NULL",
                    "role_id"               => "int(10) unsigned NOT NULL",
                    "role_name"             => "varchar(255) DEFAULT NULL",
                    "role_desc"             => "varchar(255) DEFAULT NULL",
                ],
                Table::PRIMARY_KEY => ["node_id"]
            ],

            UserRole::class  => [
                Table::TABLE_NAME => "cp_user_roles",
                Table::TABLE_ID   => "user_role_id",
                Table::FIELDS     => [
                    "user_role_id"          => "int(11) NOT NULL AUTO_INCREMENT",
                    "user_id"               => "int(10) unsigned NOT NULL",
                    "role_id"               => "int(10) unsigned NOT NULL",
                ],
                Table::PRIMARY_KEY => ["user_role_id"]
            ],
            PasswordHistory::class => [
                Table::TABLE_NAME => "cp_user_password_history",
                Table::TABLE_ID   => "user_id",
                Table::FIELDS     => [
                    "user_id"               => "int(11) DEFAULT NULL",
                    "password"              => "varchar(255) DEFAULT NULL",
                    "store_date"            => "datetime DEFAULT NULL",
                ],
                Table::PRIMARY_KEY => ["user_id"]
            ]
        ]);
    }

    function initScript($include_templates)
    {

        print("\tInserting admin user...\n");
        $admin_user = [
            "user_id"               => 1,
            "username"              => "sys_admin",
            "email"                 => $this->parameters["admin"],
            "firstname"             => "Gazda",
            "lastname"              => "Rendszer",
            "status_id"             => 1,
            "password"              => null,
        ];
        $prepared_statement = ConnectionManager::getInstance()->getConnection()->prepare("
            INSERT INTO cp_users (user_id, username, email, firstname, lastname, status_id, password) 
            VALUES (:user_id, :username, :email, :firstname, :lastname, :status_id, :password)");
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
            "role_desc" => "Admin/Rendsz ergazda",
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
            NestedSetSource::TABLE_NAME => "cp_roles",
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


}