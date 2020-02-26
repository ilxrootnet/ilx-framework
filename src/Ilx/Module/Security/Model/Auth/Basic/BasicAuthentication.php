<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\Model\User;
use Kodiak\Security\Model\Authentication\AuthenticationInterface;
use Kodiak\Security\Model\Authentication\AuthenticationTaskResult;
use PandaBase\Exception\AccessDeniedException;

class BasicAuthentication extends AuthenticationInterface
{

    /**
     * @param array $credentials
     * @return AuthenticationTaskResult
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public function login(array $credentials): AuthenticationTaskResult
    {
        $configuration = $this->getConfiguration();

        $username = $credentials["username"];
        $passwordCandidate = $credentials["password"];

        // Ellenőrizzük, hogy a user létezik-e már a rendszerben
        /** @var User $userCandidate */
        $userCandidate = User::getUserByUsername($username);


        // If the username doesnt exist, we stop the auth process with error.
        if(!$userCandidate->isValidUsername()) {
            return new AuthenticationTaskResult(false, null);
        }

        // BasicUserData betöltése
        $basicUser = BasicUserData::fromUserId($userCandidate["user_id"]);

        // Check password
        if(!$this->checkPbkdf2ByPassword($basicUser->getHashedPassword(), $passwordCandidate)) {
            // Ha nem volt megfelelő a jelszó növeljük a login countert.
            $basicUser->increaseFailedLoginCounter(true);
            return new AuthenticationTaskResult(false, 'PASSWORD_ERROR');
        }

        // Check lockout
        if (intval($configuration["max_failed_login_count"]) <= $basicUser->getFailedLoginCount()) {

            // TODO: innen kell folytatni a lock out time-mal

            return new AuthenticationTaskResult(false, 'USER_LOCKED');
        }

        // Check password expiry
        if (!$allowExpiry) {
            if (!$userCandidate["password_expire"] || $userCandidate["password_expire"]<date('Y-m-d H:i:s')) {
                return new AuthenticationTaskResult(false, 'PASSWORD_EXPIRED');
            }
        }

        $userCandidate->unLock(); // reset the faild login count to 0

        unset($userCandidate["password"]);
        unset($userCandidate["mfa_secret"]);

        return new AuthenticationTaskResult(true, $userCandidate);
    }

    public function register(array $credentials): AuthenticationTaskResult
    {
        // TODO: Implement register() method.
    }

    public function deRegister(array $credentials): AuthenticationTaskResult
    {
        // TODO: Implement deRegister() method.
    }

    public function resetPassword(array $credentials): AuthenticationTaskResult
    {
        // TODO: Implement resetPassword() method.
    }

    public function changePassword(array $credentials): AuthenticationTaskResult
    {
        // TODO: Implement changePassword() method.
    }
}