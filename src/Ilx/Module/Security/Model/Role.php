<?php


namespace Ilx\Module\Security\Model;


use Basil\DataSource\NestedSetSource;
use Basil\Tree;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\AccessDeniedException;
use PandaBase\Exception\ConnectionNotExistsException;
use PandaBase\Record\SimpleRecord;

class Role extends SimpleRecord
{
    /**
     * Törli a role-t a paraméterben kapott user_id-ról.
     *
     * @param int $user_id
     * @param int $role_id
     * @throws AccessDeniedException
     */
    public static function removeUserFrom($user_id, $role_id) {
        $user_role = UserRole::getObject($user_id, $role_id);
        if($user_role->isValid()) {
            $user_role->remove();
        }
    }

    /**
     * Visszaadja a role-t ábrázoló objektumot a fából.
     *
     * @param int $role_id
     * @return \Basil\Node\Node|null
     * @throws ConnectionNotExistsException
     */
    public static function getTreeObject($role_id) {

        $res = ConnectionManager::fetchAssoc("SELECT * FROM cp_roles WHERE role_id = :role_id",[
            "role_id" => $role_id
        ]);

        if(count($res) < 1) {
            return null;
        }

        $source = new NestedSetSource([
            NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
            NestedSetSource::TABLE_NAME => "cp_roles",
            NestedSetSource::NODE_ID    => "node_id",
            NestedSetSource::ROOT_ID    => 1,
            NestedSetSource::LEFT       => "node_lft",
            NestedSetSource::RIGHT      => "node_rgt"
        ]);
        return Tree::from($source, $res["node_id"]);
    }
}