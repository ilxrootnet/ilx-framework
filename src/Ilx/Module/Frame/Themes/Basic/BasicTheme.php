<?php


namespace Ilx\Module\Frame\Themes\Basic;


use Ilx\Module\Frame\Themes\Theme;

class BasicTheme extends Theme
{

    public function getSourcePath()
    {
        return __DIR__;
    }

    /**
     * Téma neve.
     *
     * @return string
     */
    public function getName()
    {
        return "basic";
    }

    public function getFramesPath()
    {
        return [
            "basic" => "frame.twig",
            "basic_auth" => "auth/auth_frame.twig"
        ];
    }

    public function getLoginForm()
    {
        return "basic/auth/login.twig";
    }

    public function getRegistrationForm()
    {
        return "basic/auth/registration.twig";
    }

    public function getAuthenticationFrame()
    {
        return "basic_auth";
    }

    /**
     * Visszaadja a javascript fájlok tömbjét, amikből a minified javascript készül.
     *
     * @return array
     */
    public function getJsFiles()
    {
        return [
            $this->getSourcePath()."js/0_jquery-3.4.1.min.js",
            $this->getSourcePath()."js/bootstrap.bundle.min.js",
        ];
    }

    /**
     * Visszaadja a css és/vagy less fájlok tömbjét, amikből a minified css készül.
     *
     * @return array
     */
    public function getStyleFiles()
    {
        return [
            $this->getSourcePath()."js/bootstrap.min.css",
        ];
    }
}