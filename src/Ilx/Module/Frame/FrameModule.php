<?php


namespace Ilx\Module\Frame;

use Ilx\Ilx;
use Ilx\Module\Frame\Model\Frame;
use Ilx\Module\Frame\Themes\Basic\BasicTheme;
use Ilx\Module\Frame\Themes\Theme;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Resource\ResourceModule;
use Ilx\Module\Resource\ResourcePath;
use Ilx\Module\Security\Frame\SecurityTheme;
use Ilx\Module\Twig\TwigModule;

class FrameModule extends IlxModule
{
    const OVERWRITE = "overwrite";
    const THEMES = "themes";
    const DEFAULT_FRAME = "default_frame";
    const AUTH_THEME = "auth_theme";

    const PAGE_TITLE = "page_title";
    const STYLESHEETS = "stylesheets";
    const JAVASCRIPTS = "javascripts";
    const IMAGES = "images";

    // frame név -> téma összerendelés
    const FRAMES = "frames";

    static $themes_mapping = [
        "basic" => BasicTheme::class,
        "security" => SecurityTheme::class
    ];

    private $auth_theme_class_name = null;

    function defaultParameters()
    {
        return [
            /*
             * Létező erőforrások felülírása
             *
             * Ha igaz, az install és update fázisban is felírja a már létező erőforrásfájlokat (css, js, image fájlok).
             * Célszerű emiatt false értéken tartani és csak indokolt esetben változtatni.
             */
            FrameModule::OVERWRITE => false,

            /*
             * Témák listája
             *
             * A téma listában felsorolt témák lesznek elérhetőek az alkalmazásban.
             * Itt hivatkozhatunk vagy egy ismert téma nevére ($themes_mapping)-ben definiáltak, vagy egy általunk definiált sémára
             * aminek megadjuk az osztályának a nevét namespace-szel együtt.
             *
             */
            FrameModule::THEMES => [
                "basic"
            ],

            /*
             * Az alapméretezett téma neve.
             *
             * Ha egy twig renderhez nincsen beállítva, hogy azt milyen keretben kell megejeleníteni, akkor ezt a témát
             * fogja használni
             */
            FrameModule::DEFAULT_FRAME => "basic",

            /*
             * Az authentikációhoz használt téma neve.
             *
             * A Frame ez alapján határozza meg, hogy melyik témával és formokkal kell megjeleníteni az authentikációs
             * felületeket.
             *
             * Ha nem egyik téma sem megfelelő saját témát kell készíteni, majd azt beállítani
             */
            FrameModule::AUTH_THEME => "basic",

            /*
             * A megjelenített cím
             */
            FrameModule::PAGE_TITLE  => "PageTitle",

            /*
             * Az alábbi mezőket automatikusan tölti ki a rendszer. Ezeket ne állítsd a modules.json-ből
             */
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
                "frames" => $this->parameters[FrameModule::FRAMES],
                "auth_theme" => $this->auth_theme_class_name
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

        foreach ($this->parameters[FrameModule::THEMES] as $theme_name) {
            $this->addTheme($theme_name, $moduleManager);
            print("\t- Added '$theme_name' theme\n");
        }

        if($this->auth_theme_class_name == null) {
            print("\t- WARNING: The authentication theme has not been set.\n");
        }

        # default frame beállítása
        $default = $this->parameters[FrameModule::DEFAULT_FRAME];
        $twig_module->setFrame("default", DIRECTORY_SEPARATOR.$default.DIRECTORY_SEPARATOR."frame.twig");
        $this->parameters[FrameModule::FRAMES]["default"] = $default;
        print("\t- '$default' has been set as default frame\n");

    }

    function initScript($include_templates)
    {
        // A fájlok másolását a Twig modulon keresztül a resource modul végzi, így itt nincs tennivaló.
    }

    public function addStyleSheet($theme_name, $stylesheet_path, $link=false, $overwrite = false) {

        $css_files = self::iterateOnDir($stylesheet_path, null);
        $this->parameters[FrameModule::STYLESHEETS][$theme_name] = [];
        foreach ($css_files as $css_file) {
            if($css_file == ".DS_Store") {
                continue;
            }

            $this->parameters[FrameModule::STYLESHEETS][$theme_name][] = Ilx::cssPath(true).DIRECTORY_SEPARATOR.
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
        $this->parameters[FrameModule::JAVASCRIPTS][$theme_name] = [];
        foreach ($js_files as $js_file) {
            if($js_file == ".DS_Store") {
                continue;
            }

            $this->parameters[FrameModule::JAVASCRIPTS][$theme_name][] =
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
        $this->parameters[FrameModule::IMAGES][$theme_name] = [];
        foreach ($images_files as $image_file) {
            if($image_file == ".DS_Store") {
                continue;
            }

            $this->parameters[FrameModule::IMAGES][$theme_name][] =
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

    /**
     * @param string $theme_name
     * @param ModuleManager $module_manager
     */
    public function addTheme($theme_name, $module_manager) {
        $overwrite = $this->parameters[FrameModule::OVERWRITE];

        /*
         * Ha be van regisztrálva a $themes_mapping-be akkor a név alapján betöltjük
         */
        if(in_array($theme_name, array_keys(self::$themes_mapping))) {
            $theme_class = self::$themes_mapping[$theme_name];
            /** @var Theme $theme */
            $theme = new $theme_class();
        }
        // Amúgy azt feltételezzük, hogy a frame_name a namespace-szel ellátott osztály
        else {
            /** @var Theme $theme */
            $theme = new $theme_name();
        }

        # View-k regisztrálása
        /** @var TwigModule $twig_module */
        $twig_module = $module_manager::get("Twig");
        $twig_module->addTemplatePath($theme->getViewPath(),
            $theme->getName(),
            false,
            false);
        # a frame-eket még regisztrálni kell, mint új frame.
        foreach ($theme->getFramesPath() as $frame_name => $frame_path) {
            $this->parameters[FrameModule::FRAMES][$frame_name] = $theme->getName();
            $twig_module->setFrame($frame_name, $theme->getName().DIRECTORY_SEPARATOR.$frame_path);
        }

        // Ha egyezik a név, akkor beállítjuk auth_theme-nek
        if($this->parameters[FrameModule::AUTH_THEME] == $theme->getName()) {
            $this->auth_theme_class_name = get_class($theme);
        }

        $this->addStyleSheet($theme->getName(), $theme->getCssPath(), false, $overwrite);
        $this->addJavascript($theme->getName(), $theme->getJsPath(), false, $overwrite);
        $this->addImages($theme->getName(), $theme->getImagesPath(), false, $overwrite);
    }

    private static function iterateOnDir($base, $dir_offset) {
        if(!file_exists($base.DIRECTORY_SEPARATOR.$dir_offset)) {
            return [];
        }
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