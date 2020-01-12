<?php


namespace Ilx\Module\Security\Model;


use Kodiak\Security\Model\User\AnonymUser;
use Kodiak\Security\Model\User\AuthenticatedUserInterface;
use Kodiak\Security\Model\User\Role as BaseRole;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;
use PandaBase\Exception\ConnectionNotExistsException;
use PandaBase\Record\SimpleRecord;

class User extends SimpleRecord  implements AuthenticatedUserInterface
{

    /**
     * User role-ok listája
     * @var array
     */
    private $roles = null;

    public function getHashedPassword(): ?string
    {
        return $this["password"];
    }

    public function clear(): void
    {
        // nothing to do
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

    /**
     * @return array
     * @throws ConnectionNotExistsException
     */
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
        try {
            return in_array($role, $this->getRoles());
        } catch (ConnectionNotExistsException $e) {
            return false;
        }
    }

    // TODO: megszüntetni ezt a metódust
    public function get2FASecret()
    {

    }

    public static function getUserByUserId(int $user_id): AuthenticatedUserInterface
    {
        try {
            return new User(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE user_id=:user_id", [
                "user_id" => $user_id
            ]));
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public static function getUserByUsername(string $username): AuthenticatedUserInterface
    {
        try {
            return new User(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE username=:username", [
                "username" => $username
            ]));
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public static function getUserByEmail(string $email): AuthenticatedUserInterface
    {
        try {
            return new User(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE email=:email", [
                "email" => $email
            ]));
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public static function getUserFromSecuritySession(?int $user_id, ?string $username, ?array $roles): AuthenticatedUserInterface
    {
        try {
            return new User($user_id);
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public function isRoot()
    {
        return false;
    }
}