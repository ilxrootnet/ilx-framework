<?php


namespace Ilx\Module\Security\Model;


use Basil\DataSource\NestedSetSource;
use Basil\Tree;
use InvalidArgumentException;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Exception\ConnectionNotExistsException;
use PandaBase\Record\SimpleRecord;
use Kodiak\Security\Model\User\Role as KodiakRole;

class Role extends SimpleRecord
{

    const ROLE_TABLE_NAME = "roles";

    /**
     * Visszaadja a role-t ábrázoló objektumot a fából.
     *
     * @param int $role_id
     * @return \Basil\Node\Node|null
     * @throws ConnectionNotExistsException
     */
    public static function getTreeObject($role_id) {

        $res = ConnectionManager::fetchAssoc("SELECT * FROM roles WHERE role_id = :role_id",[
            "role_id" => $role_id
        ]);

        if(count($res) < 1) {
            return null;
        }

        $source = new NestedSetSource([
            NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
            NestedSetSource::TABLE_NAME => "roles",
            NestedSetSource::NODE_ID    => "node_id",
            NestedSetSource::ROOT_ID    => 1,
            NestedSetSource::LEFT       => "node_lft",
            NestedSetSource::RIGHT      => "node_rgt"
        ]);
        return Tree::from($source, $res["node_id"]);
    }

    /** Visszadja a paraméterben kapott role_name-hez tartozó azonosítót. Ha nem létezik a role_name
     * InvalidArgumentException-t dob.
     *
     * @param string $role_name
     * @return mixed
     */
    public static function getIdByName($role_name) {
        $result = ConnectionManager::fetchAssoc(
            "SELECT * FROM ".Role::ROLE_TABLE_NAME." WHERE role_name=:role_name", [
            "role_name" => $role_name
        ]);
        if(count($result) < 1) {
            throw new InvalidArgumentException("Unknown role name: $role_name");
        }
        return $result["role_id"];
    }


    public static function getDefaultRoleStructure() {

        function addChild(&$parent, $child) {
            if(!isset($parent["children"])) {
                $parent["children"] = [];
            }
            $parent["children"][] = $child;
        }
        /*
         * TODO: Ha kell majd 2FA ezt megnézni, hogy kell-e
        $pending_user = [
            "node_id"   => 5,
            "role_id"   => KodiakRole::PENDING_USER,
            "role_name" => "pending_user",
            "role_desc" => "Pending User"
        ];
        */
        $anon_user = [
            "node_id"   => 4,
            "role_id"   => KodiakRole::ANON_USER,
            "role_name" => "anon_user",
            "role_desc" => "Unknown User"
        ];

        $auth_user = [
            "node_id"   => 3,
            "role_id"   => KodiakRole::AUTH_USER,
            "role_name" => "auth_user",
            "role_desc" => "Authenticated User"
        ];
        addChild($auth_user, $anon_user);

        $super_user = [
            "node_id"   => 2,
            "role_id"   => KodiakRole::SUP_USER,
            "role_name" => "super_user",
            "role_desc" => "Super User"
        ];
        addChild($super_user, $auth_user);

        $admin =  [
            "node_id"   => 1,
            "role_id"   => KodiakRole::ADMIN,
            "role_name" => "admin",
            "role_desc" => "Admin/Rendszergazda"
        ];
        addChild($admin, $super_user);

        return $admin;
    }
}