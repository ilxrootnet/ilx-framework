<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\ConnectionNotExistsException;
use PandaBase\Record\SimpleRecord;

class PasswordHistory extends SimpleRecord
{
    /**
     * Benne van-e a jelszó a user jelszótörténetében
     * @param int $user_id
     * @param string $new_password
     * @param int $limit
     * @return bool
     * @throws ConnectionNotExistsException
     */
    public static function isInHistory($user_id, $new_password, $limit) {
        $list = ConnectionManager::fetchAll("
            SELECT * 
            FROM auth_basic_password_history
            WHERE user_id = :userid 
            ORDER BY store_date DESC 
            limit ".$limit, [
                "userid" => $user_id
        ]);
        if (is_array($list)) {
            foreach ($list as $old) {
                $salt = substr($old["password"], 0, 64);
                $hashed_new = BasicAuthentication::hashPassword($new_password, $salt);
                if ($hashed_new->output == $old["password"]) return false;
            }
        }
        return true;
    }

    /**
     * Hozzáadja a jelszót a felhasználó jelszó historyjához.
     *
     * @param int $user_id
     * @param string $new_password
     * @throws \PandaBase\Exception\AccessDeniedException
     */
    public static function addPasswordToHistory($user_id, $new_password) {
        $pass_hist = new PasswordHistory([
            "user_id"               => $user_id,
            "password"              => $new_password,
            "store_date"            => date("Y-m-d H:i:s")
        ]);
        ConnectionManager::persist($pass_hist);
    }

    /**
     * Ellenőrzi a jelszó erősségét. True, ha megfelelően erős.
     * @param string $pwd
     * @return bool
     */
    public static function checkPasswordComplexity($pwd) {
        $pw_ok = true;

        if (strlen($pwd) < 10) {
            $pw_ok = false;
        }

        if (!preg_match("#[0-9]+#", $pwd)) {
            $pw_ok = false;
        }

        if (!preg_match("#[a-z]+#", $pwd)) {
            $pw_ok = false;
        }

        if (!preg_match("#[A-Z]+#", $pwd)) {
            $pw_ok = false;
        }

        return $pw_ok;
    }
}