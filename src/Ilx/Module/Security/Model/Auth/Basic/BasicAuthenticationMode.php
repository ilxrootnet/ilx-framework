<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use PandaBase\Connection\Scheme\Table;

class BasicAuthenticationMode extends AuthenticationMode
{

    public static function name()
    {
        return SecurityModule::TYPE_BASIC;
    }

    public function userClass()
    {
        return BasicUser::class;
    }

    public function getAuthenticationInterface()
    {
        // TODO: Implement getAuthenticationInterface() method.
    }

    public function routes()
    {
        // TODO: Implement routes() method.
    }

    public function tables()
    {
        return [
            BasicUser::class  => [
                Table::TABLE_NAME => "users",
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
        ];
    }
}