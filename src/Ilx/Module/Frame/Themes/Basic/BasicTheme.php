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
}