<?php


namespace Ilx\Module;


use ArrayAccess;


abstract class IlxModule implements ArrayAccess
{

    /**
     * Modul paramétereket tároló tömb.
     *
     * @var array
     */
    protected $parameters;


    /**
     * IlxModule konstruktor.
     *
     * @param array $module_parameters A kívánt modul beállítások, amik érvényre jutnak a module indításánál.
     */
    function __construct($module_parameters) {
        $this->parameters = array_merge($this->defaultParameters(), $module_parameters);
    }


    /**
     * Visszatér a modul paramétereinek alapméretezett értékeivel.
     *
     * @return array
     */
    abstract function defaultParameters();


    /**
     * Visszatér a modul működéséhez szükséges környezeti változókkal.
     *
     * @return array
     */
    abstract function environmentalVariables();


    /**
     * Visszatér a modul működéséhez szükséges route-okkal. Egy route-nak az alábbi módon kell kinéznie:
     * [
     *    "method" => "GET", // vagy "POST", "PUT", ... valid HTTP method legyen
     *    "url" => "/page/{page_id}",
     *    "handler" => "PageController::page" // Contlorrel osztály :: osztály metódusa
     *
     *    // opcionális mezők
     *    "page_frame" => milyen twig fájl keretbe renderelje a visszaadott tartalmat
     * ]
     *
     * @return array
     */
    abstract function routes();


    /**
     * Visszatér a modul által biztosított ServiceProvider-ek listájával.
     *
     * @return array
     */
    abstract function serviceProviders();


    /**
     * Visszaadja a modul által biztosított hook-okat.
     *
     * @return array
     */
    abstract function hooks();


    /**
     * A metódusban lehet végrehajtani azokat a feladatokat amelyek szükségesek a modul helyes inicializálásához.
     * Például ha van függősége más moduloktól, ezeket a függőségeket itt lehet ellenőrizni, illetve más modulokban
     * szükséges beállításokat érvényrejuttatni.
     *
     * Fontos: A konfigurációs fájlhoz szükséges hook, provider, environmental változók nem itt kerülnek átadásra!
     *
     * @param ModuleManager $moduleManager
     * @return mixed
     */
    abstract function bootstrap(ModuleManager $moduleManager);


    /**
     * Modul telepítő szkript, ami lefuttatja a modul megfelelő használatához szükséges első lépéseket. Például egy
     * authentikációs modul létrehozza az adatbázisban a szükséges táblákat a helyes működéshez, stb,
     * @param bool $include_templates Ha igaz, akkor a template-ket is másolni kell
     */
    abstract function initScript($include_templates);


    public function offsetExists($offset)
    {
        return isset($this->parameters[$offset]);
    }


    public function offsetGet($offset)
    {
        return $this->parameters[$offset];
    }


    public function offsetSet($offset, $value)
    {
        $this->parameters[$offset] = $value;
    }


    public function offsetUnset($offset)
    {
        unset($this->parameters[$offset]);
    }
}