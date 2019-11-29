<?php


namespace Ilx\Module\Resource;


use http\Exception\InvalidArgumentException;
use Ilx\Ilx;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Kodiak\Core\KodiConf;

/**
 * Class ResourceModule
 *
 * Feladata, hogy a rendszerhez tartozó erőforrásokat menedzselje
 *
 * @package Ilx\Module\Resource
 */
class ResourceModule extends IlxModule
{

    private $resources = [
        "css"   => [],
        "js"    => [],
        "images"=> [],
        "views" => []
    ];


    function addViewPath($path, $module_name, $copy_type, $overwrite) {
        $this->resources["views"][] = new ResourcePath($path, $module_name, $copy_type, $overwrite);
    }

    function addCssPath($path, $module_name, $copy_type, $overwrite) {
        $this->resources["css"][] = new ResourcePath($path, $module_name, $copy_type, $overwrite);
    }

    function addJsPath($path, $module_name, $copy_type, $overwrite) {
        $this->resources["js"][] = new ResourcePath($path, $module_name, $copy_type, $overwrite);
    }

    function addImagesPath($path, $module_name, $copy_type, $overwrite) {
        $this->resources["images"][] = new ResourcePath($path, $module_name, $copy_type, $overwrite);
    }


    function defaultParameters()
    {
        return [];
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
        return [];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        // Nothing to do here
    }

    function initScript($include_templates)
    {
        // Csak akkor másolunk, ha be van kapcsolva a template-k másolása
        if($include_templates) {
            print("Copying resources ...\n");

            // Létrehozzuk a szükséges mappákat
            @mkdir(Ilx::webPath());
            @mkdir(Ilx::viewPath());
            @mkdir(Ilx::cssPath());
            @mkdir(Ilx::jsPath());
            @mkdir(Ilx::imagesPath());

            print("\tCopying twig files ...\n");
            ResourceModule::copyResources($this->resources["views"], Ilx::viewPath());
            print("\tCopying css files...\n");
            ResourceModule::copyResources($this->resources["css"], Ilx::cssPath());
            print("\tCopying js files...\n");
            ResourceModule::copyResources($this->resources["js"], Ilx::jsPath());
            print("\tCopying image files...\n");
            ResourceModule::copyResources($this->resources["images"], Ilx::imagesPath());
            print("\tCreating web directory...\n");
            ResourceModule::recursive_copy(__DIR__.DIRECTORY_SEPARATOR."Templates", Ilx::webPath(), ResourcePath::HARD_COPY);
        }
        else {
            print("Copying resources has been skipped\n");
        }
    }

    /**
     * Átmásolja az erőforrás típust a megadott helyekre.
     *
     * @param ResourcePath[] $resources Erőforrásokhoz útvonal
     * @param string $dst
     */
    private static function copyResources($resources, $dst) {
        foreach ($resources as $resource) {
            if($resource->getModuleName() == null) {
                $module_dst = $dst;
            } else {
                $module_dst = $dst.DIRECTORY_SEPARATOR.$resource->getModuleName();
            }

            if(!file_exists($module_dst) || $resource->isOverwrite()) {
                print("\t\tCopying (type=".$resource->getCopyType().") from ".$resource->getPath()."\n");
                self::recursive_copy($resource->getPath(), $module_dst, $resource->getCopyType());
            }
            else {
                print("\t\tSkipped existing path: ".$resource->getPath()."\n");
            }
        }
    }

    private static function recursive_copy($src, $dst, $copy_type) {
        $dir = opendir($src);

        if($copy_type == ResourcePath::SOFT_COPY) {
            @symlink($src,$dst);
            return;
        }
        else {
            @mkdir($dst);
        }

        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recursive_copy($src .'/'. $file, $dst .'/'. $file, $copy_type);
                }
                else {
                    copy($src .'/'. $file,$dst .'/'. $file);
                }
            }
        }
        closedir($dir);
    }
}




















