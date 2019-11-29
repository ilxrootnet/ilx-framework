<?php


namespace Ilx\Module\Frame;


use Ilx\Module\Frame\Model\Frame;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FrameServiceProvider implements ServiceProviderInterface
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
        $pimple['frame'] = function ($c) use($configuration) {
            return new Frame($configuration);
        };
    }
}