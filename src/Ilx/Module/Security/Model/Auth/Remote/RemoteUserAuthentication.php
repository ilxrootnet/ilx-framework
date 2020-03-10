<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use Ilx\Module\Security\Model\User;
use Kodiak\Security\Model\Authentication\AuthenticationInterface;
use Kodiak\Security\Model\Authentication\AuthenticationTaskResult;
use PandaBase\Connection\ConnectionManager;

/**
 * Class RemoteUserAuthentication
 *
 * Célja, hogy egy távoli rendszer felhasználói tudjanak authentikálni a rendszerbe. Ebben az esetben csak minimális
 * felhasználói adatot tárolunk, jelszót soha.
 *
 * Az authentikáció mindig a távoli rendszeren történik meg, aminek kimenete alapján engedünk be felhasználókat.
 *
 * Első bejelentkezésnél mentjük mint új user. Minden új bejelentkezésnél felülírjuk a korábbi user adatokat
 *
 * @package Ilx\Module\Security\Model\Auth
 */
class RemoteUserAuthentication extends AuthenticationInterface
{

    public function login(array $credentials): AuthenticationTaskResult
    {
        $remote_config = $this->getConfiguration();
        $json_credentials = json_encode($credentials);

        $ch = curl_init($remote_config["url"]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $remote_config["http_method"] == null ? "POST" : $remote_config["http_method"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_credentials);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_credentials),
            'Authorization: Bearer '.$remote_config["token"]
        ]);

        // Távoli login hívás
        $result = curl_exec($ch);
        if($result == false) {
            return new AuthenticationTaskResult(false, "Unknown error during the remote login");
        }
        $result = json_decode($result, true);


        // Ha sikeres volt
        if($result["success"] == true) {
            // User adatok preprocesszálása
            $remote_data = $result["data"];
            $user_data = [
                "username" => $remote_data["username"],
                "email" => $remote_data["email"],
                "firstname" => $remote_data["firstname"],
                "lastname" => $remote_data["lastname"]
            ];

            // Ellenőrizzük, hogy a user létezik-e már a rendszerben
            /** @var User $user */
            $user = User::getUserByUsername($user_data["username"]);

            if(!$user->isValid()) {
                // Ha új, user létre kell hozni
                $user = new User($user_data);
                ConnectionManager::persist($user);
                $remoteUserData = new RemoteUserData([
                    "user_id" => $user["user_id"],
                    "external_id" => $remote_data["userid"],
                    "last_login" => date('Y-m-d H:i:s')
                ]);
                ConnectionManager::persist($remoteUserData);
            }
            else {
                if(!$user->isActive()) {
                    return new AuthenticationTaskResult(false, "Deactivated user");
                }

                // Ha létezik, frissítjük a létező user adatait
                $user->setAll($user_data);
                ConnectionManager::persist($user);
                $remoteUser = RemoteUserData::fromUserId($user["user_id"]);
                $remoteUser["last_login"] = date('Y-m-d H:i:s');
                ConnectionManager::persist($remoteUser);
            }
            return new AuthenticationTaskResult(true, $user);
        }
        // Ha sikertelen
        else {
            return new AuthenticationTaskResult(false, "Wrong username or password");
        }
    }

    public function register(array $credentials): AuthenticationTaskResult
    {
        return new AuthenticationTaskResult(false, "Registration operation is forbidden.");
    }

    public function deRegister(array $credentials): AuthenticationTaskResult
    {
        return new AuthenticationTaskResult(false, "Deregistration operation is forbidden.");
    }

    public function resetPassword(array $credentials): AuthenticationTaskResult
    {
        return new AuthenticationTaskResult(false, "Reset password operation is forbidden.");
    }

    public function changePassword(array $credentials): AuthenticationTaskResult
    {
        return new AuthenticationTaskResult(false, "Change password operation is forbidden");
    }
}