<?php


namespace Ilx\Module\Menu;


use Kodiak\Application;
use Kodiak\ServiceProvider\TwigProvider\ContentProvider\ContentProvider;

class MenuContentProvider extends ContentProvider
{
    public function getValue()
    {
        return Application::get("menu");
    }
}