<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;
use PandaBase\Record\SimpleRecord;

class RemoteUserData extends SimpleRecord
{
    /**
     * @param int $user_id
     * @return RemoteUserData
     * @throws AccessDeniedException
     */
    public static function fromUserId($user_id) {
        return new RemoteUserData(ConnectionManager::fetchAssoc(
            "SELECT * FROM auth_remote WHERE user_id=:user_id",
            [
            "user_id" => $user_id
        ]));
    }
}