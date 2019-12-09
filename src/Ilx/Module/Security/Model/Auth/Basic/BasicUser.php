<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Kodiak\Security\Model\User\AuthenticatedUserInterface;
use PandaBase\Record\SimpleRecord;

class BasicUser extends SimpleRecord implements AuthenticatedUserInterface
{

    public function getHashedPassword(): ?string
    {
        // TODO: Implement getHashedPassword() method.
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }

    public function getUsername(): ?string
    {
        // TODO: Implement getUsername() method.
    }

    public function isValidUsername(): bool
    {
        // TODO: Implement isValidUsername() method.
    }

    public function getUserId(): ?int
    {
        // TODO: Implement getUserId() method.
    }

    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }

    public function hasRole($role): bool
    {
        // TODO: Implement hasRole() method.
    }

    public function get2FASecret()
    {
        // TODO: Implement get2FASecret() method.
    }

    public static function getUserByUserId(int $user_id): AuthenticatedUserInterface
    {
        // TODO: Implement getUserByUserId() method.
    }

    public static function getUserByUsername(string $username): AuthenticatedUserInterface
    {
        // TODO: Implement getUserByUsername() method.
    }

    public static function getUserByEmail(string $email): AuthenticatedUserInterface
    {
        // TODO: Implement getUserByEmail() method.
    }

    public static function getUserFromSecuritySession(?int $user_id, ?string $username, ?array $roles): AuthenticatedUserInterface
    {
        // TODO: Implement getUserFromSecuritySession() method.
    }

    public function isRoot()
    {
        // TODO: Implement isRoot() method.
    }
}