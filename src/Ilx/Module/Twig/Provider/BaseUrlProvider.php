<?php


namespace Ilx\Module\Twig\Provider;


use Kodiak\ServiceProvider\TwigProvider\Twig;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class BaseUrlProvider
 *
 * Beregisztrálja twig-be a base_url metódust.
 *
 * @package Ilx\Module\Twig\Provider
 */
class BaseUrlProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        //url_generate függvény hozzáadása a twighez
        $pimple->extend('twig', function ($twig, $c) {
            /** @var Twig $mytwig */
            $mytwig = $twig;

            $base_url_function = new \Twig_SimpleFunction("base_url", function (){
                return sprintf(
                    "%s://%s",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME']
                );
            });
            $mytwig->getTwigEnvironment()->addFunction($base_url_function);
            return $mytwig;
        });
    }
}