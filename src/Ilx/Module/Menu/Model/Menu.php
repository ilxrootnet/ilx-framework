<?php


namespace Ilx\Module\Menu\Model;



use Basil\DataSource\NestedSetSource;
use Basil\Node\Node;
use Basil\Tree;
use PandaBase\Connection\ConnectionManager;

class Menu
{
    const ROOT_ID = 1;

    /**
     * @var Node
     */
    private $menu_tree;

    public function __construct($configuration)
    {
        $table_name = $configuration["table"];

        $this->menu_tree = Tree::from(new NestedSetSource([
            NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
            NestedSetSource::TABLE_NAME => $table_name,
            NestedSetSource::NODE_ID    => "node_id",
            NestedSetSource::ROOT_ID    => 1,
            NestedSetSource::LEFT       => "node_lft",
            NestedSetSource::RIGHT      => "node_rgt"
        ]), self::ROOT_ID);
        $this->menu_tree->subtree();
    }

    public function children() {
        return $this->menu_tree->children();
    }
}