<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Ilx\Module\Security\Model\Auth\User;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;

class BasicUser extends User
{
    /**
     * @throws AccessDeniedException
     */
    public function increaseFailedLoginCounter() {
        $counter = self::getFailedLoginCounter($this["user_id"]);
        $counter->set("failed_log_count", intval($counter->get("failed_log_count")) + 1);
        ConnectionManager::persist($counter);
    }

    /**
     * @return int
     * @throws AccessDeniedException
     */
    public function getFailedLoginCount() {
        $counter = self::getFailedLoginCounter($this["user_id"]);
        return intval($counter->get("failed_log_count"));
    }

    /**
     * @throws AccessDeniedException
     */
    public function resetFailedLoginCounter() {
        $counter = self::getFailedLoginCounter($this["user_id"]);
        $counter->set("failed_log_count", 0);
        ConnectionManager::persist($counter);
    }

    /**
     * @param $user_id
     * @return FailedLoginCount
     * @throws AccessDeniedException
     */
    private static function getFailedLoginCounter($user_id) {
        $counter = new FailedLoginCount($user_id);
        if(!$counter->isValid()) {
            $counter = new FailedLoginCount([
                "user_id" => $user_id,
                "failed_login_count" => 0
            ]);
            ConnectionManager::persist($counter);
        }
        return $counter;
    }
}