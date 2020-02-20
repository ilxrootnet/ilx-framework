<?php


namespace Ilx\Module\Theme\Themes\Basic;



use Ilx\Module\Theme\Model\AuthTheme;
use Ilx\Module\Theme\Model\Theme;

class BasicTheme extends Theme implements AuthTheme
{
    /**
     * Téma neve.
     *
     * @return string
     */
    public function getName()
    {
        return "basic";
    }


    public function getFrameList()
    {
        return [
            "basic" => "frame.twig",
            "basic_auth" => "auth/auth_frame.twig"
        ];
    }

    /**
     * Visszaadja a javascript fájlok tömbjét, amikből a minified javascript készül.
     *
     * @return array
     */
    public function getJsFiles()
    {
        return [];
    }

    /**
     * Visszaadja a css és/vagy less fájlok tömbjét, amikből a minified css készül.
     *
     * @return array
     */
    public function getStyleFiles()
    {
        return [];
    }

    /*
     * AuthTheme implementáció
     */

    public function getLoginForm()
    {
        return "basic/auth/login.twig";
    }

    public function getRegistrationForm()
    {
        return "basic/auth/registration.twig";
    }

    public function getFrame()
    {
        return "basic_auth";
    }
}