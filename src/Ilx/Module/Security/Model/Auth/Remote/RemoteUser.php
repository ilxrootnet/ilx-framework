<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use Ilx\Module\Security\Model\User;
use Kodiak\Security\Model\User\AnonymUser;
use Kodiak\Security\Model\User\AuthenticatedUserInterface;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;

class RemoteUser extends User
{
    /**
     * @var RemoteUserData
     */
    private $remote_data = null;

    private function loadUserData() {
        $this->remote_data = RemoteUserData::fromUserId($this["user_id"]);
    }

    public function getExternalId() {
        if($this->remote_data == null) {
            $this->loadUserData();
        }
        return $this->remote_data["external_id"];
    }

    public function setLastLogin($last_login) {
        if($this->remote_data == null) {
            $this->loadUserData();
        }
        $this->remote_data["last_login"] = $last_login;
        ConnectionManager::persist($this->remote_data);
    }

    public function getLastLogin() {
        if($this->remote_data == null) {
            $this->loadUserData();
        }
        return $this->remote_data["last_login"];
    }

    public static function getUserByUserId(int $user_id): AuthenticatedUserInterface
    {
        try {
            return new RemoteUser(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE user_id=:user_id", [
                "user_id" => $user_id
            ]));
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public static function getUserByUsername(string $username): AuthenticatedUserInterface
    {
        try {
            return new RemoteUser(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE username=:username", [
                "username" => $username
            ]));
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public static function getUserByEmail(string $email): AuthenticatedUserInterface
    {
        try {
            return new RemoteUser(ConnectionManager::fetchAssoc("SELECT * FROM users WHERE email=:email", [
                "email" => $email
            ]));
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }

    public static function getUserFromSecuritySession(?int $user_id, ?string $username, ?array $roles): AuthenticatedUserInterface
    {
        try {
            return new RemoteUser($user_id);
        } catch (AccessDeniedException $e) {
            return new AnonymUser();
        }
    }
}