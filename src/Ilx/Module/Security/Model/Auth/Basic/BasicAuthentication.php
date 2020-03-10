<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\Model\User;
use Kodiak\Security\Model\Authentication\AuthenticationInterface;
use Kodiak\Security\Model\Authentication\AuthenticationTaskResult;
use PandaBase\Connection\ConnectionManager;
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


        // If the username doesn't exist or it is inactive, we stop the auth process with error.
        if(!$userCandidate->isValidUsername() or !$userCandidate->isActive()) {
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
            $basicUser->resetFailedLoginCounter(true);
        }

        // Ellenőrizzük, hogy lejárt-e a jelszó
        if($configuration["check_password_expiration"] &&
            $basicUser->isPasswordExpired($configuration["password_expiration_time_in_secs"])) {
            return new AuthenticationTaskResult(false, 'PASSWORD_EXPIRED');
        }


        return new AuthenticationTaskResult(true, $userCandidate);
    }

    /**
     * @param array $credentials
     * @return AuthenticationTaskResult
     * @throws AccessDeniedException
     */
    public function register(array $credentials): AuthenticationTaskResult
    {
        // Check mandatory fields existence
        $fields = ["username", "email", "firstname", "lastname", "password", "repassword"];
        foreach ($fields as $field) {
            if(!isset($credentials[$field])) {
                $authResult = new AuthenticationTaskResult(false, "MISSING_FIELD");
                return $authResult;
            }
        }

        if((User::getUserByUsername($credentials["username"]))->isValidUsername()) {
            $authResult = new AuthenticationTaskResult(false, "USERNAME_EXISTS");
            return $authResult;
        }

        if((User::getUserByEmail($credentials["email"]))->isValidUsername()) {
            $authResult = new AuthenticationTaskResult(false, "EMAIL_EXISTS");
            return $authResult;
        }

        if($credentials["password"] !== $credentials["repassword"]) {
            $authResult = new AuthenticationTaskResult(false, "MISMATCHED_PASSWORDS");
            return $authResult;
        }
        $credentials["password"] = $this->hashPassword($credentials["password"])->output;

        /*
         * Alap user létrehozása
         */
        $user = new User([
            "username"  => $credentials["username"],
            "email"     => $credentials["email"],
            "firstname" => $credentials["firstname"],
            "lastname"  => $credentials["lastname"]
        ]);
        ConnectionManager::getInstance()->persist($user);

        $basicUser = new BasicUserData([
            "user_id"               => $user["user_id"],
            "password"              => $user["password"],
            "last_password_mod"     => date("Y-m-d H:i:s")
        ]);
        ConnectionManager::getInstance()->persist($basicUser);


        return new AuthenticationTaskResult(true, $user);
    }

    /**
     * @param array $credentials
     * @return AuthenticationTaskResult
     */
    public function deRegister(array $credentials): AuthenticationTaskResult
    {
        return new AuthenticationTaskResult(false, "Deregistration operation is forbidden.");
    }

    /**
     * @param array $credentials
     * @return AuthenticationTaskResult
     * @throws \Exception
     */
    public function resetPassword(array $credentials): AuthenticationTaskResult
    {
        $configuration = $this->getConfiguration();

        $resetToken = $credentials["token"];
        $basicUser = BasicUserData::fromResetToken($resetToken);
        if($basicUser->isValid()) {
            return new AuthenticationTaskResult(false, "MISMATCHED_TOKENS");
        }

        if($basicUser->isResetTokenExpired($configuration["reset_token_expiration_in_secs"])) {
            return new AuthenticationTaskResult(false, "MISMATCHED_TOKENS");
        }

        // Új jelszók egyformák
        if($credentials["password"] !== $credentials["repassword"]) {
            $authResult = new AuthenticationTaskResult(false, "MISMATCHED_PASSWORDS");
            return $authResult;
        }

        if (!PasswordHistory::checkPasswordComplexity($credentials["password"])) {
            return new AuthenticationTaskResult(false, 'PASSWORD_COMPLEXITY_FAIL');
        }

        $basicUser["password"] = $this->hashPassword($credentials["password"])->output;
        if (PasswordHistory::isInHistory($basicUser->getUserId(), $basicUser["password"], $configuration["password_history_limit"])) {
            return new AuthenticationTaskResult(false, 'PASSWORD_IN_HISTORY');
        }

        ConnectionManager::getInstance()->persist($basicUser);
        PasswordHistory::addPasswordToHistory($basicUser->getUserId(), $basicUser["password"]);
        return new AuthenticationTaskResult(true, null);
    }

    /**
     * @param array $credentials
     * @return AuthenticationTaskResult
     * @throws \Exception
     */
    public function changePassword(array $credentials): AuthenticationTaskResult
    {
        $configuration = $this->getConfiguration();

        $username = $credentials["username"];
        /** @var User $userCandidate */
        $userCandidate = User::getUserByUsername($username);


        // If the username doesnt exist, we stop the auth process with error.
        if(!$userCandidate->isValidUsername() or !$userCandidate->isActive()) {
            return new AuthenticationTaskResult(false, null);
        }

        // BasicUserData betöltése
        $basicUser = BasicUserData::fromUserId($userCandidate["user_id"]);

        // Check password
        if(!$this->checkPbkdf2ByPassword($basicUser->getHashedPassword(), $credentials["old_password"])) {
            return new AuthenticationTaskResult(false, 'PASSWORD_ERROR');
        }


        // Megnézzük, hogy ki lett-e zárva már korábban. Ha eltelt lock_out_time_in_secs, akkor ezen tovább fog lépni.
        if($basicUser->isLockedOut($configuration["lock_out_time_in_secs"])) {
            return new AuthenticationTaskResult(false, 'USER_LOCKED');
        }

        // Új jelszók egyformák
        if($credentials["password"] !== $credentials["repassword"]) {
            $authResult = new AuthenticationTaskResult(false, "MISMATCHED_PASSWORDS");
            return $authResult;
        }

        // New != Old
        if($credentials["old_password"] == $credentials["password"]) {
            return new AuthenticationTaskResult(false, "PASSWORD_IN_HISTORY");
        }

        if (!PasswordHistory::checkPasswordComplexity($credentials["password"])) {
            return new AuthenticationTaskResult(false, 'PASSWORD_COMPLEXITY_FAIL');
        }

        $basicUser["password"] = $this->hashPassword($credentials["password"])->output;
        if (PasswordHistory::isInHistory($userCandidate->getUserId(), $basicUser["password"], $configuration["password_history_limit"])) {
            return new AuthenticationTaskResult(false, 'PASSWORD_IN_HISTORY');
        }

        ConnectionManager::getInstance()->persist($basicUser);
        PasswordHistory::addPasswordToHistory($userCandidate->getUserId(), $basicUser["password"]);
        return new AuthenticationTaskResult(true, null);
    }
}