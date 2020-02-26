<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use Ilx\Module\Security\Model\User;
use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\Security\Model\User\Role;
use PandaBase\Connection\Scheme\Table;

/**
 * Class RemoteAuthenticationMode
 *
 *
 * Paraméterek
 *  - url: End-point ahonnan elérhető a távoli authentikáció
 *  - http_method: HTTP method
 *  - token: Biztonsági token, ami a HTTP headerben utazik
 *
 * @package Ilx\Module\Security\Model\Auth\Remote
 */
class RemoteAuthenticationMode extends AuthenticationMode
{

    public static function name()
    {
        return SecurityModule::AUTH_REMOTE;
    }

    public function userClass()
    {
        return User::class;
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
            RemoteUserData::class  => [
                Table::TABLE_NAME => "auth_remote",
                Table::TABLE_ID   => "remote_auth_id",
                Table::FIELDS     => [
                    "remote_auth_id"=> "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "user_id"       => "int(10) unsigned NOT NULL",
                    "external_id"   => "int(10) unsigned NOT NULL",
                    "last_login"    => "datetime",
                ],
                Table::PRIMARY_KEY => ["remote_auth_id"]
            ]
        ];
    }

    public function permissions() {
        return [
            "^\/auth\/remote\/login$" => [Role::ANON_USER]
        ];
    }
}