<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use Ilx\Module\Security\Model\Auth\User;
use PandaBase\Connection\ConnectionManager;

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
}