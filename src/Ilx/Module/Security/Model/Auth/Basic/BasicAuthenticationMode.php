<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\Controller\AuthController;
use Ilx\Module\Security\SecurityModule;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\Security\Model\User\Role;
use PandaBase\Connection\Scheme\Table;

/**
 * Class BasicAuthenticationMode
 *
 * Paraméterek:
 *  - SEND_REGISTER_CONFIRMATION: Ha igaz, akkor a bejelentkezéshez szükséges az email cím visszaigazolása. Ilyenkor küldünk regisztráció után emailt is
 *
 *  - DEFAULT_ROLES: Alap role azonosítók tömbje, amelyeket a user a regisztráció végén megkap.
 *
 *
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

    const SEND_REGISTER_CONFIRMATION = "send_reg_confirmation";
    const DEFAULT_ROLES = "default_roles";

    const MAX_FAILED_LOGIN_COUNT = "max_failed_login_count";
    const LOCK_OUT_TIME_IN_SECS = "lock_out_time_in_secs";
    const CHECK_PASSWORD_EXPIRATION = "check_password_expiration";
    const PASSWORD_HISTORY_LIMIT = "password_history_limit";
    const REST_TOKEN_EXPIRATION_IN_SECS = "reset_token_expiration_in_secs";


    public static function name()
    {
        return SecurityModule::AUTH_BASIC;
    }

    public function getAuthenticationInterface()
    {
        return new BasicAuthentication($this->parameters);
    }

    public function routes()
    {
        $routes = [
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

            "renderLoginFrame" => [
                "method" => "GET",
                "url" => "/auth/login",
                "handler" => AuthController::class."::renderLogin"
            ],
            "renderRegistrationFrame" => [
                "method" => "GET",
                "url" => "/auth/registration",
                "handler" => AuthController::class."::renderRegistration"
            ],
            "renderResetPasswordRequestFrame" => [
                "method" => "GET",
                "url" => "/auth/reset_password",
                "handler" => AuthController::class."::renderResetPasswordRequest"
            ],
            "renderResetPasswordFrame" => [
                "method" => "GET",
                "url" => "/auth/basic/reset_password/{token}",
                "handler" => AuthController::class."::renderResetPassword"
            ],
            "renderChangePasswordFrame" => [
                "method" => "GET",
                "url" => "/auth/change_password",
                "handler" => AuthController::class."::renderChangePassword"
            ],

            "renderEmailVerification" => [
                "method" => "GET",
                "url" => "/auth/basic/verify",
                "handler" => BasicAuthController::class."::verifyEmailAddress"
            ],
            "sendEmailVerification" => [
                "method" => "POST",
                "url" => "/auth/basic/verify/{user_id}",
                "handler" => BasicAuthController::class."::sendVerificationAddress"
            ],
        ];

        if($this->parameters[BasicAuthenticationMode::SEND_REGISTER_CONFIRMATION]) {
            $routes["renderEmailVerification"] = [
                "method" => "GET",
                "url" => "/auth/basic/verify",
                "handler" => BasicAuthController::class."::verifyEmailAddress"
            ];

            $routes["sendEmailVerification"] = [
                "method" => "POST",
                "url" => "/auth/basic/verify/{user_id}",
                "handler" => BasicAuthController::class."::sendVerificationAddress"
            ];
        }
        return $routes;
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
                    "failed_login_count"    => "int(10) NOT NULL DEFAULT '0'",
                    "is_verified"           => "tinyint(1) NOT NULL DEFAULT '0'",
                    "verification_token"    => "varchar(256) DEFAULT NULL"
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
        return [
            "^\/auth\/basic\/login$" => [Role::ANON_USER],
            "^\/auth\/basic\/logout$" => [Role::AUTH_USER],
            "^\/auth\/basic\/register$" => [Role::ANON_USER],
            "^\/auth\/basic\/change_password$" => [Role::AUTH_USER],
            "^\/auth\/basic\/reset_password_request$" => [Role::ANON_USER],
            "^\/auth\/basic\/reset_password$" => [Role::ANON_USER],
            "^\/auth\/basic\/reset_password\/\w*$" => [Role::ANON_USER],
        ];
    }
}