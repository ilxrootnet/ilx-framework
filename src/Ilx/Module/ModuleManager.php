<?php


namespace Ilx\Module;


use Ilx\Configuration;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class ModuleManager
 *
 * Feladata, hogy nyilvántartsa, hogy milyen modulok érhetőek el az aktuális környezetben.
 *
 *
 * @package Ilx\Module
 */
class ModuleManager
{

    private static $moduleManager = null;

    /**
     * Modulok tömbje
     * @var IlxModule[]
     */
    private $modules;


    /**
     * ModuleManager constructor.
     * @param IlxModule[] $modules
     */
    private function __construct($modules)
    {
        $this->modules = $modules;
    }


    /**
     * A paraméterben kapott Ilx config fájl elérési útvonala alapján inicializál egy ModuleManager példányt és azt
     * visszadja a kimenetén. A program futása sor
     *
     * @param array $modules_config Ilx config
     * @return ModuleManager
     */
    public static function init($modules_config) {
        $modules = [];
        foreach ($modules_config["modules"] as $module_name => $parameters) {
            print("\tLoading module: $module_name\n");
            $modules[$module_name] = self::loadModule($module_name, $parameters);
        }

        self::$moduleManager = new ModuleManager($modules);

        return self::$moduleManager;
    }


    /**
     * Visszadja a ModuleManager példányt. Ha még nem lett inicializálva (ModuleManager.init(...)), akkor null-lal
     * fig visszatérni.
     *
     * @return ModuleManager|null
     */
    public static function getInstance() {
        if(self::$moduleManager == null) {
            throw new RuntimeException("ModuleManager has not been initialized. Call ModuleManager.init(...) 
            before get() or getInstance()");
        }

        return self::$moduleManager;
    }


    /**
     * Visszadja a paraméterben kapott IlxModule-t. Ha nem létezik a modul név, null a visszatérési érték.
     * Ha nem lett inicializálva a ModuleManager RunTimeException fog keletkezni.
     *
     * @param string $module_name
     * @return IlxModule|null
     */
    public static function get($module_name) {
        $moduleManager = ModuleManager::getInstance();

        if(!isset($moduleManager->modules[$module_name])) {
            throw new InvalidArgumentException("Missing module: $module_name. Make sure, that the required module has been added to the module configuration.");
        }
        return $moduleManager->modules[$module_name];
    }


    /**
     * Végrehajtja a bootstrap metódusát az összes modulon a definiálásuk sorrendjében.
     *
     */
    public function bootstrapModules() {
        foreach ($this->modules as $module) {
            $module->bootstrap($this);
        }
    }


    /**
     * A paraméterben kapott konfigurációs fájlt updateli az akutális modul beállítások alapján.
     *
     * @return Configuration
     */
    public function collectConfiguration() {
        $configuration = new Configuration();
        foreach ($this->modules as $module) {
            // Környezeti változók
            foreach ($module->environmentalVariables() as $name => $variable) {
                $configuration->addEnvironmentalVariable($name, $variable);
            }

            // Hook-ok hozzáadása
            foreach ($module->hooks() as $hook) {
                // Hogy az sima vagy router hook az 1. index alatt lévő értékből derül ki
                $configuration->addHook($hook[0], $hook[1]);
            }

            // ServiceProvider-ek hozzáadása
            foreach ($module->serviceProviders() as $serviceProvider) {
                $configuration->addServiceProvider($serviceProvider);
            }

            // Útvonalak hozzáadása
            foreach ($module->routes() as $route_name => $route) {
                $configuration->addRoute($route_name, $route);
            }

            if($module->errorHandler() != null) {
                $configuration->addErrorHandler($module->errorHandler());
            }
        }
        return $configuration;
    }

    /**
     * Futtatja a modulok telepítési szkriptjeit.
     * @param bool $include_templates
     */
    public function runInstallScripts($include_templates) {
        foreach ($this->modules as $module) {
            $module->initScript($include_templates);
        }
    }

    /**
     * Beltölti (példányosítja) a paraméterben kapott modult. Ha nem találja az elérhető osztályok között
     * InvalidArgumentException-t fog dobni.
     *
     * @param string $name Modul neve (Module suffix nélkül!!!)
     * @param array $parameters Modul paraméterek
     * @return IlxModule
     */
    private static function loadModule($name, $parameters) {
        $name = $name."Module";
        $pathToFile = self::searchFile($name);
        $namespace = substr(
            explode(' ',
                array_values(
                    preg_grep("/namespace.+/", file($pathToFile))
                )[0]
            )[1],
            0, -2);

        if($namespace == null) {
            throw new InvalidArgumentException("Module cannot be found: ". $name);
        }
        $class = $namespace.'\\'.$name;

        return new $class($parameters);
    }


    /**
     * Megkeresi a paraméterben kapott fájlt. Ha a paraméterben szereplő dir mappa nincs megadva (null), akkor az
     * aktuális working directory-ból kiindulva, egyébként a paraméterben kapott könyvtárból kiindulva rekurzívan keresi
     * meg a paraméterben kapott fájlnevet (kiterjesztés nélkül!) és visszatérési értékében visszaadja a hozzá tartozó
     * elérési útvonalat.
     *
     * Ha nem létezik a fájl a visszatérési értéke null.
     *
     * @param string $filename A keresett fájl
     * @param string|null $dir
     * @return string|null
     */
    private static function searchFile($filename, $dir = null){

        if($dir == null) {
            $dir = getcwd();
        }

        $files = scandir($dir);
        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $path_parts = explode("/", $path);
                $file_name_parts = explode(".", $path_parts[count($path_parts)-1]);
                if(count($file_name_parts) > 1) {
                    $class_name = $file_name_parts[count($file_name_parts)-2];
                    $extension = $file_name_parts[count($file_name_parts)-1];
                    if($extension === "php" && strtolower($class_name) == strtolower($filename)) {
                        return $path;
                    }
                }
            } else if($value != "." && $value != "..") {
                $res = self::searchFile($filename, $path);
                if($res != null) {
                    return $res;
                }
            }
        }
        return null;
    }
}