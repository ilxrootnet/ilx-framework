<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\Model\Auth\Remote\RemoteAuthController;
use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use PandaBase\Connection\Scheme\Table;

class BasicAuthenticationMode extends AuthenticationMode
{

    public static function name()
    {
        return SecurityModule::AUTH_BASIC;
    }

    public function userClass()
    {
        return BasicUser::class;
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
            // TODO: kiszervezni egy userbe a közös dolgokat. Az authra egyedi dolgok mehetnek küéön táblákba
            // BasicUsernek nem lesz táblája, őt csak örököl a User-ből
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
                    "last_login"            => "datetime DEFAULT NULL ",
                    "reset_token"           => "varchar(200) DEFAULT NULL",
                    "failed_login_count"    => "int(10) NOT NULL DEFAULT '0'"

                ],
                Table::PRIMARY_KEY => ["user_id"]
            ],
            FailedLoginCount::class  => [
                Table::TABLE_NAME => "auth_basic_failed_log_count",
                Table::TABLE_ID   => "user_id",
                Table::FIELDS     => [
                    "user_id"               => "int(10) unsigned NOT NULL",
                    "failed_login_count"    => "int(10) NOT NULL DEFAULT '0'"

                ],
                Table::PRIMARY_KEY => ["user_id"]
            ],
        ];
    }

    public function permissions()
    {
        // TODO: Implement permissions() method.
    }
}