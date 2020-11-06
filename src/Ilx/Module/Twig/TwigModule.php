<?php


namespace Ilx\Module\Twig;


use Ilx\Ilx;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Resource\ResourceModule;
use Ilx\Module\Resource\ResourcePath;
use Ilx\Module\Twig\Provider\BaseUrlProvider;
use Kodiak\ServiceProvider\TwigProvider\Twig;
use Kodiak\ServiceProvider\TwigProvider\TwigServiceProvider;
use Kodiak\ServiceProvider\UrlGeneratorProvider\UrlGeneratorProvider;

class TwigModule extends IlxModule
{
    const FRAME_PATH = DIRECTORY_SEPARATOR."frame".DIRECTORY_SEPARATOR;

    private $frames = [];


    function defaultParameters()
    {
        return [
            "path" => Ilx::viewPath(),
            "default" => TwigModule::FRAME_PATH."frame.twig",
            "content_providers" => []
        ];
    }

    /**
     * Bemeneteként kapott könyvtárat ($template_path) beregisztrálja, mint Twig elérési útvonal.
     *
     *
     * @param string $template_path Könyvtár, amiben a view-k vannak.
     * @param string $module_name Név, ami alatt hivatkozni lehet majd a twig fájlokra.
     * @param bool $to_link Ha igaz, akkor egy szimbolikus link lesz az alkalmazás buildben, nem másolja át a fájlokat.
     * @param bool $overwrite Ha igaz, akkor felülírja az esetlegesen létező fájlokat az útvonal alatt
     */
    function addTemplatePath($template_path, $module_name, $to_link, $overwrite) {
        /** @var ResourceModule $resource_module */
        $resource_module = ModuleManager::get("Resource");
        $resource_module->addViewPath(
            $template_path,
            $module_name,
            ($to_link ? ResourcePath::SOFT_COPY : ResourcePath::HARD_COPY),
            $overwrite
        );

    }

    /**
     * Frame beregisztrálása a névvel és elérési útvonalával. Fontos, hogy magának a twig fájlnak valamelyik template
     * path alatt kell lennie, mert ezeknek a fájloknak a másolása nem fog megtörténni.
     *
     * Például: A 'myproj' nevű projekt twig fájljait a .../myviews/ alatt fejlesztjük, ahol van egy saját frame-ünk
     * .../myviews/proj_frame/super_frame.twig elérési útvonal alatt.
     *
     * Első lépésben az addTemplatePath('.../myviews/', 'myproj', true) hívással biztosítjuk, hogy másoljuk a twig
     * fájlokat. Ezután az setFrame('tetszoleges_nev','/myproj/proj_frame/super_frame.twig') metódus hívással már
     * helyesen be tudjuk regisztrálni a twiget.
     *
     * Az alapméretezett frame-et a setFrame("default", ...) metódus hívással lehet átállítani.
     *
     * @param string $name
     * @param string $frame_path
     *
     */
    function setFrame($name, $frame_path) {
        $this->frames[$name] = $frame_path;
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        # Nincs mit tenni, ha vannak alap twig fájlok, azokat külön a frame modulban kell beállítani
    }

    function initScript($include_templates)
    {
        # Nincs mit tenni, az esetleges twig fájlokat a resource modul másolja
    }

    function routes()
    {
        // A twig modul nem ad hozzá route-okat a rendszerhez
        return [];
    }

    function serviceProviders()
    {
        return [
            [
                "class_name" => TwigServiceProvider::class,
                "parameters" => [
                    Twig::TWIG_PATH => $this->parameters["path"],
                    Twig::PAGE_TEMPLATE_PATH => $this->frames,
                    Twig::CONTENT_PROVIDERS => $this->parameters["content_providers"]
                ],
            ],
            [
                "class_name" => UrlGeneratorProvider::class,
                "parameters" => [],
            ],
            [
                "class_name" => BaseUrlProvider::class,
                "parameters" => [],
            ]
        ];
    }

    function environmentalVariables()
    {
        // A twig modul nem ad hozzá környezeti változókat a rendszerhez
        return [];
    }

    function hooks()
    {
        // A twig modul nem ad hozzá hook-okat a rendszerhez
        return [];
    }

    public function addContentProvider($contentProvider) {
        $this->parameters["content_providers"][] = $contentProvider;
    }
}