<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use PandaBase\Connection\Scheme\Table;

/**
 * Class BasicAuthenticationMode
 *
 * Paraméterek:
 *  - max_failed_login_count: Hány hibás bejelentkezés engedélyezett
 *  - mi legyen a lock out time?
 *
 * @package Ilx\Module\Security\Model\Auth\Basic
 */
class BasicAuthenticationMode extends AuthenticationMode
{

    public static function name()
    {
        return SecurityModule::AUTH_BASIC;
    }

    public function userClass()
    {
        return BasicUserData::class;
    }

    public function getAuthenticationInterface()
    {
        return new BasicAuthentication($this->parameters);
    }

    public function routes()
    {
        return [
            "basicLoginRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/login",
                "handler" => BasicAuthController::class."::login"
            ],
            "basicRegisterRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/register",
                "handler" => BasicAuthController::class."::register"
            ],
            "basicChangePasswordRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/change_password",
                "handler" => BasicAuthController::class."::changePassword"
            ],
            "basicPasswordResetRequestRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/change_password",
                "handler" => BasicAuthController::class."::passwordResetRequest"
            ],
            "basicPasswordResetRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/change_password",
                "handler" => BasicAuthController::class."::passwordReset"
            ],
            "basicLogoutRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/logout",
                "handler" => BasicAuthController::class."::logout"
            ],
        ];
    }

    public function tables()
    {
        return [
            BasicUserData::class  => [
                Table::TABLE_NAME => "auth_basic",
                Table::TABLE_ID   => "basic_auth_id",
                Table::FIELDS     => [
                    "basic_auth_id"         => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "user_id"               => "int(10) unsigned NOT NULL",
                    "password"              => "varchar(256) DEFAULT NULL",
                    "password_expire"       => "datetime DEFAULT NULL",
                    "last_login"            => "datetime DEFAULT NULL ",
                    "reset_token"           => "varchar(200) DEFAULT NULL",
                    "failed_login_count"    => "int(10) NOT NULL DEFAULT '0'"
                ],
                Table::PRIMARY_KEY => ["basic_auth_id"]
            ],
            // TODO: Password history
        ];
    }

    public function permissions()
    {
        // TODO: Implement permissions() method.
    }
}