<?php


namespace Ilx\Module\Theme;

use Ilx\Ilx;
use Ilx\Module\Theme\Model\Frame;
use Ilx\Module\Theme\Themes\Basic\BasicTheme;
use Ilx\Module\Theme\Themes\Theme;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Resource\ResourceModule;
use Ilx\Module\Resource\ResourcePath;
use Ilx\Module\Security\Frame\SecurityTheme;
use Ilx\Module\Twig\TwigModule;

class ThemeModule extends IlxModule
{
    const OVERWRITE = "overwrite";
    const THEMES = "themes";
    const DEFAULT_FRAME = "default_frame";
    const AUTH_THEME = "auth_theme";

    const PAGE_TITLE = "page_title";


    function defaultParameters()
    {
        return [
            /*
             * Létező erőforrások felülírása
             *
             * Ha igaz, az install és update fázisban is felülírja a már létező erőforrásfájlokat (css, js, image fájlok).
             * Célszerű false értéken tartani és csak indokolt esetben változtatni.
             */
            ThemeModule::OVERWRITE => false,

            /*
             * Témák listája
             *
             * A téma listában felsorolt témák lesznek elérhetőek az alkalmazásban.
             * Itt hivatkozhatunk vagy egy ismert téma nevére ($themes_mapping)-ben definiáltak, vagy egy általunk definiált sémára
             * aminek megadjuk az osztályának a nevét namespace-szel együtt.
             *
             */
            ThemeModule::THEMES => [
                [BasicTheme::class, ResourcePath::SOFT_COPY]
            ],

            /*
             * Az alapméretezett frame neve.
             *
             * A frame névnek szerepelnie kell a témák által definiált frame nevek között.
             * Ha egy twig renderhez nincsen beállítva, hogy azt milyen keretben kell megjeleníteni, akkor ezt a témát
             * fogja használni
             */
            ThemeModule::DEFAULT_FRAME => "basic",

            /*
             * Az authentikációhoz használt téma osztályána kneve.
             *
             * A ThemeModule ez alapján határozza meg, hogy melyik témával és formokkal kell megjeleníteni az
             * authentikációs felületeket.
             *
             * Ha egyik téma sem megfelelő saját témát kell készíteni, majd azt beállítani
             */
            ThemeModule::AUTH_THEME => BasicTheme::class,

            /*
             * A megjelenített cím
             */
            ThemeModule::PAGE_TITLE  => "PageTitle",
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
                "title" => $this->parameters[ThemeModule::PAGE_TITLE],
                "auth_theme" => $this->parameters[self::AUTH_THEME]
            ]
        ]];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        print("Bootstraping ThemeModule...\n");

        print("\t- Registering themes...\n");
        $default = $this->parameters[ThemeModule::DEFAULT_FRAME];
        $default_frame_path = null;
        foreach ($this->parameters[ThemeModule::THEMES] as $theme_row) {
            $theme_class_name = $theme_row[0];
            $theme_copy_type = $theme_row[1];

            /** @var Theme $theme_ins */
            $theme_ins = new $theme_class_name();
            $this->registerTheme($theme_ins, $moduleManager, $theme_copy_type);
            print("\t\t- Added '$theme_class_name' theme\n");

            if(in_array($default, $theme_ins->getFrameList())) {
                $default_frame_path = $theme_ins->getName().DIRECTORY_SEPARATOR.$theme_ins->getFrameList()[$default];
            }
        }

        /** @var TwigModule $twig_module */
        $twig_module = $moduleManager::get("Twig");

        // Default frame beállítása
        if($default_frame_path != null) {
            $twig_module->setFrame("default", $default_frame_path);
            print("\t- '$default' has been set as default frame\n");
        }
        else {
            print("\t- Default frame has not been set\n");
        }


    }

    function initScript($include_templates)
    {
        // A fájlok másolását a Twig modulon keresztül a resource modul végzi, így itt nincs tennivaló.
    }

    /**
     * Beregisztrálja a témát.
     *
     * @param string $theme_class
     * @param string $copy_type
     */
    public function addTheme($theme_class, $copy_type) {
        $this->parameters[self::THEMES][] = [$theme_class, $copy_type];
    }

    /**
     * Beregisztrálja az authentikációs témát leíró osztályt.
     * @param string $auth_theme_class
     */
    public function addAuthTheme($auth_theme_class) {
        $this->parameters[self::AUTH_THEME] = $auth_theme_class;
    }

    /**
     * Téma beregisztrálása a többi modulban
     * @param Theme $theme
     * @param string $copy_type
     * @param ModuleManager $module_manager
     */
    private function registerTheme($theme, $copy_type, $module_manager) {
        $overwrite = $this->parameters[ThemeModule::OVERWRITE];

        // A Frame-k (plusz egyéb view-k) beregisztrálása a twig-be
        /** @var TwigModule $twig_module */
        $twig_module = $module_manager::get("Twig");
        $twig_module->addTemplatePath($theme::getFramesPath(), $theme->getName(), $copy_type == ResourcePath::SOFT_COPY, $overwrite);

        // A frame-eket még regisztrálni kell, mint új frame.
        foreach ($theme->getFrameList() as $frame_name => $frame_path) {
            $twig_module->setFrame($frame_name, $theme->getName().DIRECTORY_SEPARATOR.$frame_path);
        }


        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");

        // Javascript fájlok beállítása
        $resource_module->addJsFilePath($theme->getMinifiedJsPath(), $theme->getName(), $copy_type, $overwrite);

        // Css fájlok beállítása
        $resource_module->addCssFilePath($theme->getMinifiedCssPath(), $theme->getName(), $copy_type, $overwrite);

        // Egyéb forrásfájlok beállítása
        $resource_module->addResourcesPath($theme::getResourcesPath(), $theme->getName(), $copy_type, $overwrite);
    }

    /*
     * TODO: authTheme kezelése
     *
     *         // Ha egyezik a név, akkor beállítjuk auth_theme-nek
        if($this->parameters[ThemeModule::AUTH_THEME] == $theme->getName()) {
            $this->auth_theme_class_name = get_class($theme);
        }

     */

}