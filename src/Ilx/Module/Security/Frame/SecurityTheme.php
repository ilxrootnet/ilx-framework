<?php


namespace Ilx\Module\Security\Frame;


use Ilx\Module\Frame\Themes\Theme;

/**
 * Class SecurityTheme
 *
 * A SecurityTheme biztosítja az authentikációhoz kapcsolódó js és css fájlokat.
 *
 * @package Ilx\Module\Security\Frame
 */
class SecurityTheme extends Theme
{

    public function getName()
    {
        return "security";
    }

    public function getSourcePath()
    {
        return __DIR__.DIRECTORY_SEPARATOR."Resources";
    }

    public function getFramesPath()
    {
        return [];
    }

    public function getLoginForm()
    {
        return null;
    }

    public function getRegistrationForm()
    {
        return null;
    }

    /**
     * Visszadja a témához tartozó authentikációs frame twig relatív útvonalát.
     *
     * @return string
     */
    public function getAuthenticationFrame()
    {
        return null;
    }
}