<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\Model\User;
use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\Security\Model\User\Role;
use PandaBase\Connection\Scheme\Table;

/**
 * Class BasicAuthenticationMode
 *
 * Paraméterek:
 *  - max_failed_login_count: Hány hibás bejelentkezés engedélyezett
 *  - lock_out_time_in_secs: Kizárási idő másodpercekben, ha max_failed_login_count alkalommal sikertelen volt a bejelentkezés
 *
 *  - check_password_expiration: true|false, kell-e jelszó lejáratot ellenőrizni
 *  - password_expiration_time_in_secs: Jelszó lejárati idő másodpercekben
 *
 *  - password_history_limit: Mekkora password historyt vegyen figyelembe (darabszám)
 *
 *  - reset_token_expiration_in_secs: Meddig érvényes a reset_token másodpercekben
 *
 * @package Ilx\Module\Security\Model\Auth\Basic
 */
class BasicAuthenticationMode extends AuthenticationMode
{
    const MAIL_REG_CONFIRMATION = "registration_confirmation";
    const MAIL_PASSWORD_RESET = "password_reset";



    public static function name()
    {
        return SecurityModule::AUTH_BASIC;
    }

    public function userClass()
    {
        return User::class;
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
                "url" => "/auth/basic/reset_password_request",
                "handler" => BasicAuthController::class."::passwordResetRequest"
            ],
            "basicPasswordResetRequest" => [
                "method" => "POST",
                "url" => "/auth/basic/reset_password",
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
                    "last_password_mod"     => "datetime DEFAULT NULL",
                    "last_login"            => "datetime DEFAULT NULL ",
                    "last_login_attempt"    => "datetime DEFAULT NULL ",
                    "reset_token"           => "varchar(200) DEFAULT NULL",
                    "reset_token_date"      => "datetime DEFAULT NULL",
                    "failed_login_count"    => "int(10) NOT NULL DEFAULT '0'"
                ],
                Table::PRIMARY_KEY => ["basic_auth_id"]
            ],
            PasswordHistory::class  => [
                Table::TABLE_NAME => "auth_basic_password_history",
                Table::TABLE_ID   => "pwh_id",
                Table::FIELDS     => [
                    "pwh_id"                => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "user_id"               => "int(10) unsigned NOT NULL",
                    "password"              => "varchar(256) DEFAULT NULL",
                    "store_date"            => "datetime DEFAULT NULL"
                ],
                Table::PRIMARY_KEY => ["pwh_id"]
            ],
        ];
    }

    public function permissions()
    {
        // TODO: URL-ket beállítani, mailer modult rákötni. IP-t is lehetne figyelni
        return [
            "^\/auth\/remote\/login$" => [Role::ANON_USER],
            "^\/auth\/remote\/logout" => [Role::AUTH_USER],
        ];
    }
}