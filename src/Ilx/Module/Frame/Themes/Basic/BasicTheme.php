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
            "basic" => "frame.twig"
        ];
    }

    public function getLoginFrame()
    {
        return null;
    }

    public function getRegistrationFrame()
    {
        return null;
    }
}