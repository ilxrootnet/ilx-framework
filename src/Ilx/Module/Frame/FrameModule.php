<?php


namespace Ilx\Module\Frame;


use Ilx\Ilx;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Resource\ResourceModule;
use Ilx\Module\Resource\ResourcePath;
use Ilx\Module\Twig\TwigModule;

class FrameModule extends IlxModule
{
    const OVERWRITE = "overwrite";
    const FRAME_NAMES = "frame_names";
    const DEFAULT_FRAME = "default_frame";

    const PAGE_TITLE = "page_title";
    const STYLESHEETS = "stylesheets";
    const JAVASCRIPTS = "javascripts";

    function defaultParameters()
    {
        return [
            FrameModule::OVERWRITE => false,
            FrameModule::FRAME_NAMES => ["basic"],
            FrameModule::DEFAULT_FRAME => "basic",

            FrameModule::PAGE_TITLE  => "my page",
            FrameModule::STYLESHEETS => [],
            FrameModule::JAVASCRIPTS => []
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
            "class_name" => FrameServiceProvider::class,
            "parameters" => [
                "title" => $this->parameters[FrameModule::PAGE_TITLE],
                "stylesheets" => $this->parameters[FrameModule::STYLESHEETS],
                "javascripts" => $this->parameters[FrameModule::JAVASCRIPTS]
            ]
        ]];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        print("Bootstraping FrameModule...\n");

        print("\tRegistering FrameContentProvider...\n");
        /** @var TwigModule $twig_module */
        $twig_module = $moduleManager::get("Twig");
        $twig_module->addContentProvider([
            "class_name" => FrameContentProvider::class,
            "parameters" => [
                "name" => "frame"
            ]
        ]);


        foreach ($this->parameters[FrameModule::FRAME_NAMES] as $frame_name) {



            # kiválasztjuk a megfelelő template útvonalt
            $template_path = dirname(__FILE__).DIRECTORY_SEPARATOR."Templates".DIRECTORY_SEPARATOR.$frame_name;
            # hozzáadjuk, mint template útvonal. Ezeket mindig másoljuk és nem készül róluk szimbolikus link
            $twig_module->addTemplatePath($template_path, $frame_name, false);
            # a frame-eket még regisztrálni kell, mint új frame.
            $twig_module->setFrame($frame_name, DIRECTORY_SEPARATOR.$frame_name.DIRECTORY_SEPARATOR."frame.twig");
            print("\t- Added '$frame_name' as frame template\n");
        }

        # default frame beállítása
        $default = $this->parameters[FrameModule::DEFAULT_FRAME];
        $twig_module->setFrame("default", DIRECTORY_SEPARATOR.$default.DIRECTORY_SEPARATOR."frame.twig");
        print("\t- '$default' has been set as default frame\n");


        print("\tRegistering stylesheets...\n");

    }

    function initScript($include_templates)
    {
        // A fájlok másolását a Twig modulon keresztül a resource modul végzi, így itt nincs tennivaló.
    }

    public function addStyleSheet($group_name, $stylesheet_path, $link=false) {

        $css_files = self::iterateOnDir($stylesheet_path, null);
        foreach ($css_files as $css_file) {
            $this->parameters[FrameModule::STYLESHEETS][] =
                Ilx::cssPath(true).DIRECTORY_SEPARATOR.
                $group_name.
                $css_file;
        }

        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");
        $resource_module->addCssPath($stylesheet_path, $group_name, $link ? ResourcePath::SOFT_COPY : ResourcePath::HARD_COPY);
    }

    public function addJavascript($group_name, $javascript_path, $link = false) {

        $js_files = self::iterateOnDir($javascript_path, null);
        foreach ($js_files as $js_file) {
            $this->parameters[FrameModule::JAVASCRIPTS][] =
                Ilx::jsPath(true).DIRECTORY_SEPARATOR.
                $group_name.DIRECTORY_SEPARATOR.
                $js_file;
        }


        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");
        $resource_module->addJsPath($javascript_path, $group_name, $link ? ResourcePath::SOFT_COPY : ResourcePath::HARD_COPY);
    }

    private static function iterateOnDir($base, $dir_offset) {
        $dir = opendir($base.DIRECTORY_SEPARATOR.$dir_offset);
        $res = [];
        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $file_path = $base. DIRECTORY_SEPARATOR. $dir_offset . DIRECTORY_SEPARATOR . $file;
                if ( is_dir($file_path) ) {
                    $res = array_merge($res, self::iterateOnDir($base, $dir_offset . DIRECTORY_SEPARATOR . $file));
                }
                else {
                    $res[] = $dir_offset . DIRECTORY_SEPARATOR . $file;
                }
            }
        }
        closedir($dir);
        return $res;
    }
}