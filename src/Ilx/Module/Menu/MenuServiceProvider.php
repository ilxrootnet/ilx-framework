<?php


namespace Ilx\Module\Menu;


use Ilx\Module\Menu\Model\Menu;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MenuServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * MailerProvider constructor.
     * @param array $configuration
     */
    public function __construct($configuration)
    {

        $this->configuration = $configuration;
    }

    public function register(Container $pimple)
    {
        $configuration = $this->configuration;
        $pimple['menu'] = function ($c) use($configuration) {
            return new Menu($configuration);
        };
    }
}