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


        // Megnézzük, hogy ki lett-e zárva már korábban. Ha eltelt lock_out_time_in_secs, akkor ezen tovább fog lépni.
        if($basicUser->isLockedOut($configuration["lock_out_time_in_secs"])) {
            return new AuthenticationTaskResult(false, 'USER_LOCKED');
        }

        // Check password
        if(!$this->checkPbkdf2ByPassword($basicUser->getHashedPassword(), $passwordCandidate)) {
            // Ha nem volt megfelelő a jelszó növeljük a login countert.
            $basicUser->increaseFailedLoginCounter(true);

            // Ha elérte a user a max_failed_login_count próbálkozást kizárjuk
            if (intval($configuration["max_failed_login_count"]) <= $basicUser->getFailedLoginCount()) {
                $basicUser->setToLockedOut();
                return new AuthenticationTaskResult(false, 'USER_LOCKED');
            }
            return new AuthenticationTaskResult(false, 'PASSWORD_ERROR');
        }
        // Ha eddig eljutott tudta a jelszót. A login countert visszaállítjuk ha szükséges
        if($basicUser->getFailedLoginCount() > 0) {
            // TODO: ha valamit később biztosan módosítunk akkor ez lehet false!
            $basicUser->resetFailedLoginCounter(true);
        }

        // TODO: Ellenőrizzük, hogy lejárt-e a jelszó

        /*
         *  - check_password_expiration: true|false, kell-e jelszó lejáratot ellenőrizni
         *  - password_expiration_time_in_secs: Jelszó lejárati idő másodpercekben
         */



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