<?php


namespace Ilx;


use Kodiak\Core\KodiConf;
use Kodiak\Core\Router\SimpleRouter;
use Kodiak\Response\DefaultErrorResponse;

/**
 * Class Configuration
 *
 * @package Ilx
 */
class Configuration
{
    private $config;

    const NOT_ROUTER_HOOK = false;
    const ROUTER_HOOK = true;

    /**
     * Configuration constructor.
     * @param array $config
     */
    public function __construct($config = null)
    {
        if($config == null) {
            $this->config = self::$configuration_skeleton;
        }
        else {
            $this->config = $config;
        }

    }

    /**
     * Beolvas egy konfigurációt a paraméterben megadott elérési útvonalról.
     *
     * @param string $path
     * @return Configuration
     */
    public static function from_json($path) {
        return new Configuration(json_decode(file_get_contents($path), true));
    }

    /**
     * A paraméterben megadott útvonalra menti a konfiguráció tartalmát.
     *
     * @param string $path
     */
    public function to_json($path) {
        file_put_contents($path, json_encode($this->config, JSON_PRETTY_PRINT));
    }

    /**
     * Visszaadja a konfigurációt.
     *
     * @return array
     */
    public function get() {
        return $this->config;
    }

    /**
     * Környezeti változó hozzáadása.
     *
     * @param string $name
     * @param string $variable
     */
    public function addEnvironmentalVariable($name, $variable) {
        $this->config[KodiConf::ENVIRONMENT][$name] = $variable;
    }

    /**
     * Provider hozzáadása.
     *
     * @param array $provider
     */
    public function addServiceProvider($provider) {
        $this->config[KodiConf::SERVICES][] = $provider;
    }

    /**
     * Hook hozzáadása.
     *
     * @param array $hook
     * @param bool $router_hook
     */
    public function addHook($hook, $router_hook = false) {
        if($router_hook) {
            $this->config[KodiConf::ROUTER]["parameters"]["hooks"][] = $hook;
        }
        else {
            $this->config[KodiConf::HOOKS][] = $hook;
        }
    }

    /**
     * Útvonal hozzáadása.
     *
     * @param string $route_name
     * @param array $route
     */
    public function addRoute($route_name, $route) {
        $this->config[KodiConf::ROUTES][$route_name] = $route;
    }


    public function addErrorHandler($error_handler) {
        $this->config[KodiConf::ERROR_HANDLER] = $error_handler;
    }

    /**
     * Kodiak konfiguráció váz.
     * @var array
     */
    private static $configuration_skeleton = [

        KodiConf::ENVIRONMENT => [

        ],

        KodiConf::HOOKS => [

        ],

        KodiConf::SERVICES => [

        ],

        KodiConf::ROUTES => [

        ],

        KodiConf::ROUTER => [
            "class_name" => SimpleRouter::class,
            "parameters" => [
                "hooks" => []
            ]
        ],

        KodiConf::ERROR_HANDLER => DefaultErrorResponse::class
    ];
}