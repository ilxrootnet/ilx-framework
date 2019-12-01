<?php


namespace Ilx\Module\Frame;


use Ilx\Ilx;
use Ilx\Module\Frame\Themes\Basic\BasicTheme;
use Ilx\Module\Frame\Themes\Theme;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Resource\ResourceModule;
use Ilx\Module\Resource\ResourcePath;
use Ilx\Module\Twig\TwigModule;

class FrameModule extends IlxModule
{
    const OVERWRITE = "overwrite";
    const THEMES = "themes";
    const DEFAULT_FRAME = "default_frame";

    const PAGE_TITLE = "page_title";
    const STYLESHEETS = "stylesheets";
    const JAVASCRIPTS = "javascripts";
    const IMAGES = "images";

    // frame név -> téma összerendelés
    const FRAMES = "frames";

    static $themes_mapping = [
        "basic" => BasicTheme::class,
    ];

    function defaultParameters()
    {
        return [
            FrameModule::OVERWRITE => false,

            // Témák listája.
            FrameModule::THEMES => [
                "basic"
            ],
            // Default frame neve
            FrameModule::DEFAULT_FRAME => "basic",

            FrameModule::PAGE_TITLE  => "PageTitle",
            FrameModule::STYLESHEETS => [],
            FrameModule::JAVASCRIPTS => [],
            FrameModule::FRAMES => [],
            FrameModule::IMAGES => []
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
                "javascripts" => $this->parameters[FrameModule::JAVASCRIPTS],
                "images" => $this->parameters[FrameModule::IMAGES],
                "frames" => $this->parameters[FrameModule::FRAMES]
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

        $overwrite = $this->parameters[FrameModule::OVERWRITE];
        foreach ($this->parameters[FrameModule::THEMES] as $frame_name) {

            /*
             * Ha be van regisztrálva a $themes_mapping-be akkor a név alapján betöltjük
             */
            if(in_array($frame_name, array_keys(self::$themes_mapping))) {
                $theme_class = self::$themes_mapping[$frame_name];
                /** @var Theme $theme */
                $theme = new $theme_class();
            }
            // Amúgy azt feltételezzük, hogy a frame_name a namespace-szel ellátott osztály
            else {
                /** @var Theme $theme */
                $theme = new $frame_name();
            }

            # View-k regisztrálása
            $twig_module->addTemplatePath($theme->getViewPath(),
                $theme->getName(),
                false,
                false);
            # a frame-eket még regisztrálni kell, mint új frame.
            foreach ($theme->getFramesPath() as $frame_name => $frame_path) {
                $this->parameters[FrameModule::FRAMES][$frame_name] = $theme->getName();
                $twig_module->setFrame($frame_name, $theme->getName().DIRECTORY_SEPARATOR.$frame_path);
            }

            $this->addStyleSheet($theme->getName(), $theme->getCssPath(), false, $overwrite);
            $this->addJavascript($theme->getName(), $theme->getJsPath(), false, $overwrite);
            $this->addImages($theme->getName(), $theme->getImagesPath(), false, $overwrite);

            print("\t- Added '$frame_name' as frame template\n");
        }

        # default frame beállítása
        $default = $this->parameters[FrameModule::DEFAULT_FRAME];
        $twig_module->setFrame("default", DIRECTORY_SEPARATOR.$default.DIRECTORY_SEPARATOR."frame.twig");
        print("\t- '$default' has been set as default frame\n");

    }

    function initScript($include_templates)
    {
        // A fájlok másolását a Twig modulon keresztül a resource modul végzi, így itt nincs tennivaló.
    }

    public function addStyleSheet($theme_name, $stylesheet_path, $link=false, $overwrite = false) {

        $css_files = self::iterateOnDir($stylesheet_path, null);
        foreach ($css_files as $css_file) {
            $this->parameters[FrameModule::STYLESHEETS][$theme_name] =
                Ilx::cssPath(true).DIRECTORY_SEPARATOR.
                $theme_name.
                $css_file;
        }

        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");
        $resource_module->addCssPath($stylesheet_path,
            $theme_name,
            $link ? ResourcePath::SOFT_COPY : ResourcePath::HARD_COPY,
            $overwrite);
    }

    public function addJavascript($theme_name, $javascript_path, $link = false, $overwrite = false) {

        $js_files = self::iterateOnDir($javascript_path, null);
        foreach ($js_files as $js_file) {
            if($js_file == ".DS_Store") {
                continue;
            }

            $this->parameters[FrameModule::JAVASCRIPTS][$theme_name] =
                Ilx::jsPath(true).DIRECTORY_SEPARATOR.
                $theme_name.
                $js_file;
        }


        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");
        $resource_module->addJsPath($javascript_path,
            $theme_name,
            $link ? ResourcePath::SOFT_COPY : ResourcePath::HARD_COPY,
            $overwrite);
    }

    public function addImages($theme_name, $images_path, $link = false, $overwrite = false) {
        $images_files = self::iterateOnDir($images_path, null);
        foreach ($images_files as $image_file) {
            $this->parameters[FrameModule::IMAGES][$theme_name] =
                Ilx::imagesPath(true).DIRECTORY_SEPARATOR.
                $theme_name.
                $image_file;
        }

        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");
        $resource_module->addImagesPath($images_path,
            $theme_name,
            $link ? ResourcePath::SOFT_COPY : ResourcePath::HARD_COPY,
            $overwrite);
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