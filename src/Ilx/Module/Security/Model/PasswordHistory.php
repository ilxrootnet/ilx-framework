<?php


namespace Ilx\Module\Security\Model;


use PandaBase\Connection\ConnectionManager;
use PandaBase\Record\SimpleRecord;

class PasswordHistory extends SimpleRecord
{
    public static function getHistory($user_id, $limit=6) {
        return ConnectionManager::getInstanceRecords(PasswordHistory::class,"
            SELECT * FROM cp_user_password_history WHERE user_id = :userid ORDER BY store_date DESC limit $limit
        ", [
            "userid"=> $user_id
        ]);
    }
}