<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Kodiak\Security\Model\Authentication\AuthenticationInterface;
use Kodiak\Security\Model\Authentication\AuthenticationTaskResult;
use Kodiak\Security\Model\User\AuthenticatedUserInterface;
use PandaBase\Exception\AccessDeniedException;

class BasicAuthentication extends AuthenticationInterface
{

    /**
     * @param array $credentials
     * @return AuthenticationTaskResult
     * @throws AccessDeniedException
     */
    public function login(array $credentials): AuthenticationTaskResult
    {

        $username = $credentials["username"];
        $passwordCandidate = $credentials["password"];
        /** @var BasicUser $userCandidate */
        $userCandidate = BasicUser::getUserByUsername($username);

        // If the username doesnt exist, we stop the auth process with error.
        if(!$userCandidate->isValidUsername()) {
            return new AuthenticationTaskResult(false, null);
        }

        // Check password
        if(!$this->checkPbkdf2($userCandidate,$passwordCandidate)) {
            $userCandidate->increaseFailedLoginCounter();
            return new AuthenticationTaskResult(false, 'PASSWORD_ERROR');
        }

        // TODO: innen kell folytatni

        // Check lockout
        if ($userCandidate->isLockedOut()) {
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