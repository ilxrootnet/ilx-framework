<?php


namespace Ilx\Module\Security\Model\Auth;


use Ilx\Module\Security\Model\UserRole;
use Kodiak\Security\Model\User\AuthenticatedUserInterface;
use Kodiak\Security\Model\User\Role as BaseRole;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Record\SimpleRecord;

class RemoteUser extends SimpleRecord implements AuthenticatedUserInterface
{

    private $roles;

    public function getHashedPassword(): ?string
    {
        return null;
    }

    public function clear(): void
    {

    }

    public function getUsername(): ?string
    {
        return $this["username"];
    }

    /**
     * @return bool
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public function isValidUsername(): bool
    {
        self::getUserByUsername($this["username"]);
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

    public function hasRole($role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function get2FASecret()
    {
        // TODO: Implement get2FASecret() method.
    }

    /**
     * @param int $user_id
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByUserId(int $user_id): AuthenticatedUserInterface
    {
        return new RemoteUser($user_id);
    }

    /**
     * @param string $username
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByUsername(string $username): AuthenticatedUserInterface
    {
        return new RemoteUser(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE username=:username", [
            "username" => $username
        ]));
    }

    /**
     * @param string $email
     * @return AuthenticatedUserInterface
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function getUserByEmail(string $email): AuthenticatedUserInterface
    {
        return new RemoteUser(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE email=:email", [
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
        return new RemoteUser($user_id);
    }

    public function isRoot()
    {
        return false;
    }
}