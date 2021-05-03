<?php


namespace Ilx\Module\Security\Provider;


use Ilx\Module\Security\Model\Role;
use Kodiak\Security\Model\SecurityManager;
use Kodiak\ServiceProvider\TwigProvider\Twig;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ExtendedSecurityProvider implements ServiceProviderInterface
{
    private $configuration;

    /**
     * SecurityProvider constructor.
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }


    public function register(Container $pimple)
    {
        $conf = $this->configuration;
        $pimple['security'] = $pimple->factory(function ($c) use($conf) {
            return new SecurityManager($conf);
        });

        $pimple->extend('twig', function ($twig, $c) {
            /** @var Twig $mytwig */
            $mytwig = $twig;
            /** @var SecurityManager $securityManager */
            $securityManager = $c["security"];
            $get_user = new \Twig\TwigFunction("get_user",function() use($securityManager) {
                return $securityManager->getUser();
            });
            $mytwig->getTwigEnvironment()->addFunction($get_user);

            $has_role = new \Twig\TwigFunction("has_role",function($role) use($securityManager) {

                if(is_string($role)) {
                    $role_id = Role::getIdByName($role);
                }
                else {
                    $role_id = $role;
                }

                $user = $securityManager->getUser();
                return $user->hasRole($role_id);
            });
            $mytwig->getTwigEnvironment()->addFunction($has_role);

            return $mytwig;
        });
    }
}
