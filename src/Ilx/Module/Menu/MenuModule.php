<?php


namespace Ilx\Module\Menu;


use Basil\DataSource\ArraySource;
use Basil\DataSource\NestedSetSource;
use Basil\Tree;
use Ilx\Module\Database\DatabaseModule;
use Ilx\Module\IlxModule;
use Ilx\Module\Menu\Model\MenuItem;
use Ilx\Module\ModuleManager;
use Ilx\Module\Twig\TwigModule;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Connection\Scheme\Table;

/**
 * Class MenuModule
 *
 * @package Ilx\Module\Menu
 */
class MenuModule extends IlxModule
{

    function defaultParameters()
    {
        return [
            "structure" => [],
            "table" => "menu"
        ];
    }

    function environmentalVariables()
    {
        return [];
    }

    function routes()
    {
        return [];
    }

    function serviceProviders()
    {
        return [[
            "class_name" => MenuServiceProvider::class,
            "parameters" => [
                "table" => $this->parameters["table"]
            ]
        ]];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        /** @var TwigModule $twig_module */
        $twig_module = $moduleManager::get("Twig");
        $twig_module->addContentProvider([
            "class_name" => MenuContentProvider::class,
            "parameters" => [
                "name" => "menu"
            ]
        ]);


        /** @var DatabaseModule $database_module */
        $database_module = $moduleManager::get("Database");

        $database_module->addTables([
            MenuItem::class  => [
                Table::TABLE_NAME => $this->parameters["table"],
                Table::TABLE_ID   => "node_id",
                Table::FIELDS     => [
                    "node_id"               => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "node_lft"              => "int(10) DEFAULT NULL",
                    "node_rgt"              => "int(10) DEFAULT NULL",
                    "name"              => "varchar(255) DEFAULT NULL",
                    "title"             => "varchar(255) DEFAULT NULL",
                    "route"             => "varchar(255) DEFAULT NULL",
                ],
                Table::PRIMARY_KEY => ["node_id"]
            ]
        ]);

    }

    function initScript($include_templates)
    {
        print("Creating menu structure...\n");

        # struktúra kiiratása és node_id hozzáadása
        function rc_tree(&$node, $level = 0) {
            $t = $node["title"];
            $n = $node["name"];
            $r = $node["route"];
            print(str_repeat("\t", $level+1)."- $t (name=$n, route=$r)\n");
            foreach ($node["children"] as $child) {
                rc_tree($child, $level+1);
            }
        }
        foreach ($this->parameters["structure"] as $menu_item) {
            rc_tree($menu_item);
        }

        $roots = [
            "name"      => "root",
            "title"     => "Root",
            "route"     => null,
            "children"  => $this->parameters["structure"]
        ];

        /*
         * Töröljük, ha van korábbi elem
         */
        /** @var \PDO $pdo */
        $pdo = ConnectionManager::getInstance()->getDefault()->getDatabase();
        $table_name = $this->parameters["table"];
        $ps = $pdo->prepare("TRUNCATE TABLE $table_name");
        $ps->execute();


        Tree::convert(new ArraySource([
            ArraySource::NODE_ID => "name",
            ArraySource::CHILDREN=> "children",
            ArraySource::ROOT_ID => 1
        ], $roots), new NestedSetSource([
            NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
            NestedSetSource::TABLE_NAME => $this->parameters["table"],
            NestedSetSource::NODE_ID    => "node_id",
            NestedSetSource::ROOT_ID    => 1,
            NestedSetSource::LEFT       => "node_lft",
            NestedSetSource::RIGHT      => "node_rgt"
        ]));
        print("Menu structure has been created.\n");
    }
}