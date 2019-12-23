<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\Security\Model\User\Role;
use PandaBase\Connection\Scheme\Table;


class RemoteAuthenticationMode extends AuthenticationMode
{

    public static function name()
    {
        return SecurityModule::TYPE_REMOTE;
    }

    public function userClass()
    {
        return RemoteUser::class;
    }

    public function getAuthenticationInterface()
    {
        return new RemoteUserAuthentication($this->parameters);
    }

    public function routes()
    {
        return [
            "remoteLoginRequest" => [
                "method" => "POST",
                "url" => "/auth/remote/login",
                "handler" => RemoteAuthController::class."::login"
            ],
            "remoteLogoutRequest" => [
                "method" => "POST",
                "url" => "/auth/remote/logout",
                "handler" => RemoteAuthController::class."::logout"
            ],
        ];
    }

    public function tables()
    {
        /*
         * Remote auth specifikus osztályok kerülnek ide
         */
        return [
            RemoteUser::class  => [
                Table::TABLE_NAME => "users",
                Table::TABLE_ID   => "user_id",
                Table::FIELDS     => [
                    "user_id"               => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "username"              => "varchar(200) DEFAULT NULL",
                    "email"                 => "varchar(200) NOT NULL",
                    "firstname"             => "varchar(256) DEFAULT NULL",
                    "lastname"              => "varchar(256) DEFAULT NULL",
                    "external_id"           => "int(10)  unsigned NOT NULL"
                ],
                Table::PRIMARY_KEY => ["user_id"]
            ]
        ];
    }

    public function permissions() {
        return [
            "^\/auth\/remote\/login$" => [Role::ANON_USER]
        ];
    }
}