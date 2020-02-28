<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Exception;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;
use PandaBase\Record\SimpleRecord;

/**
 * Class BasicUserData
 *
 * A BasicUserData osztály tárolja az összes Basic authentikációhoz szükséges adatot a felhasználóról.
 *
 * @package Ilx\Module\Security\Model\Auth\Basic
 */
class BasicUserData extends SimpleRecord
{
    /**
     * BasicUserData objektum betöltése user_id alapján
     *
     * @param int $user_id
     * @return BasicUserData
     * @throws AccessDeniedException
     */
    public static function fromUserId($user_id) {
        return new BasicUserData(ConnectionManager::fetchAssoc(
            "SELECT * FROM auth_basic WHERE user_id=:user_id",
            [
                "user_id" => $user_id
            ]));
    }

    /**
     * Visszatér a felhasználó hashelt jelszavával
     *
     * @return string
     */
    public function getHashedPassword() {
        return $this["password"];
    }

    /**
     * Érvényes-e még a jelszó vagy sem. Ha igen, true amúgy false a visszatérési érték.
     *
     * @return bool
     */
    public function getLastPasswordChangeDate() {
        return true;
    }

    /**
     * Igaz, ha a lock out idő még nem járt le. Amúgy hamis.
     *
     * @param int $lock_out_time_in_secs
     * @return bool
     * @throws AccessDeniedException
     */
    public function isLockedOut($lock_out_time_in_secs) {
        // Ha a last login attempt időn belül van
        return (time() - strtotime($this["last_login_attempt"])) <= $lock_out_time_in_secs;
    }

    /**
     * Kizárja a usert a rendszerből.
     *
     * @throws Exception
     * @throws AccessDeniedException
     */
    public function setToLockedOut() {
        $this->set("last_login_attempt", date("Y-m-d H:i:s"));
        ConnectionManager::persist($this);
    }

    /**
     * Ellenőrzi, hogy a bemeneti lejárati időhöz képest lejárt-e a jelszó vagy sem.
     * @param int $expiration_time
     * @return bool
     */
    public function isPasswordExpired($expiration_time) {
        return ($expiration_time + strtotime($this["last_password_mod"])) <= time();
    }


    /*
     *
     * Failed Login Counter-re kapcsolatos metódusok
     *
     */

    /**
     * Növeli eggyel a hibás login számlálót. A változtatás nem jelenik meg azonnal az adatbázisban, ehhez menteni kell
     * magát az objektumot. Ha mégis szeretnénk menteni, akkor a $persist paramétert kell true-ra állítani.
     *
     * @param bool $persist
     * @throws \Exception
     */
    public function increaseFailedLoginCounter($persist = false) {
        $this->set("failed_log_count", intval($this->get("failed_log_count")) + 1);
        ConnectionManager::persist($this);
    }

    /**
     * Visszatér a hibás login számláló értékével.
     *
     * @return int
     * @throws AccessDeniedException
     */
    public function getFailedLoginCount() {
        return intval($this->get("failed_log_count"));
    }

    /**
     * 0-ra állítja a hibás login számlálót. A változtatás nem jelenik meg azonnal az adatbázisban, ehhez menteni kell
     * magát az objektumot. Ha mégis szeretnénk menteni, akkor a $persist paramétert kell true-ra állítani.
     *
     * @param bool $persist
     * @throws \Exception
     */
    public function resetFailedLoginCounter($persist = false) {
        $this->set("failed_log_count", 0);
        if($persist) {
            ConnectionManager::persist($this);
        }
    }
}