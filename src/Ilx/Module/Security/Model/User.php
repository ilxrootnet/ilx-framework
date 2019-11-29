<?php

namespace Ilx\Module\Security\Model;


use Kodiak\Security\Model\User\AuthenticatedUserInterface;
use Kodiak\Security\PandabaseAuthentication\PAv1Authentication;
use Kodiak\Security\Model\User\Role as BaseRole;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Record\SimpleRecord;



class User extends SimpleRecord implements AuthenticatedUserInterface
{
    /**
     * @var array
     */
    private $roles = null;

    public function getHashedPassword(): ?string
    {
        return $this["password"];
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }

    public function getUsername(): ?string
    {
        return $this["username"];
    }

    public function isValidUsername(): bool
    {
        return $this->isValid();
    }

    public function getUserId(): ?int
    {
        return $this["user_id"];
    }

    public function getRoles(): array
    {
        if($this->roles == null) {
            $this->roles = UserRole::getRoles($this["user_id"]);
        }
        array_unshift($this->roles, BaseRole::AUTH_USER);

        return $this->roles;
    }

    public function getRolesAsString(): string {
        return implode(',',$this->getRoles());
    }

    public function hasRole($role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @param $role
     * @throws \PandaBase\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function addRole($role) {
        $userRole = new UserRole([
            "user_id" => $this->getUserId(),
            "group_id"=> $role
        ]);
        ConnectionManager::persist($userRole);
    }

    /**
     * Returns with the 2FA secret
     * @return mixed
     */
    public function get2FASecret()
    {
        return $this["mfa_secret"];
    }

    public function incrementFailedPasswordCount() {
        $this["failed_login_count"] = $this["failed_login_count"]+1;
        ConnectionManager::persist($this);
    }

    public function isLockedOut() {
        if ($this["failed_login_count"]>=3 || $this["status_id"]!=1) return true;
        else return false;
    }

    public function unLock() {
        $this["failed_login_count"]=0;
        ConnectionManager::persist($this);        
    }

    public function checkPasswordHistory($new) {
        $list = PasswordHistory::getHistory($this["user_id"]);
        if (is_array($list)) {
            foreach ($list as $old) {
                $salt = substr($old["password"], 0, 64);
                $hash = PAv1Authentication::hashPassword($new, $salt);
                if ($hash->output == $old["password"]) return false;
            }
        }
        return true;
    }

    /**
     * @param $hash
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public function addPasswordToHistory($hash) {
        $password_history_record = new PasswordHistory([
            "user_id"       => $this["user_id"],
            "password"      => $hash,
            "store_date"    => date("Y-m-d H:i:s")
        ]);
        ConnectionManager::persist($password_history_record);
    }

    /**
     * @param int $user_id
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByUserId(int $user_id): AuthenticatedUserInterface
    {
        return new User($user_id);
    }

    /**
     * @param string $username
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByUsername(string $username): AuthenticatedUserInterface
    {
        return new User(ConnectionManager::fetchAssoc("SELECT * FROM cp_users WHERE username=:username", [
            "username" => $username
        ]));
    }

    /**
     * @param string $token
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByToken(string $token): AuthenticatedUserInterface
    {
        return new User(ConnectionManager::fetchAssoc("SELECT * FROM cp_users WHERE reset_token=:token", [
            "token" => $token
        ]));
    }

    /**
     * @param string $email
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByEmail(string $email): AuthenticatedUserInterface
    {
        return new User(ConnectionManager::fetchAssoc("SELECT * FROM cp_users WHERE email=:email", [
            "email" => $email
        ]));
    }

    /**
     * @param int|null $user_id
     * @param string|null $username
     * @param array|null $roles
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserFromSecuritySession(?int $user_id, ?string $username, ?array $roles): AuthenticatedUserInterface
    {
        return new User($user_id);
    }

    public function isRoot()
    {
        return false;
    }

    public function setResetToken() {
        $this["reset_token"] = bin2hex(openssl_random_pseudo_bytes(32));
        $this["password"] = "";
        $deadline = new \DateTime();
        $deadline->add(new \DateInterval('PT1H'));
        $this["password_expire"] = $deadline->format('Y-m-d H:i:s');

        ConnectionManager::persist($this);
        return $this["reset_token"];
    }

    public function updateLastLogin() {
        $this["last_login"] = date('Y-m-d H:i:s');
        ConnectionManager::persist($this);
    }

    public function setPasswordExpiry(string $interval) {
        $date = new \DateTime();
        $date->add(new \DateInterval($interval));
        $this["password_expire"] = $date->format('Y-m-d H:i:s');
        ConnectionManager::persist($this);
    }
}